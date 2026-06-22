<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// 로그 생성
use App\Services\LogService;

class Member extends Model
{
    protected $table = 'member'; // 테이블명
    protected $primaryKey = 'mb_id'; // PK
    public $timestamps = true; // created_at, updated_at 자동 관리

    protected $fillable = [
        'mb_affiliation',
        'mb_department',
        'mb_name',
        'mb_hire_date',
        'mb_retire_date'
    ];

    /**
     * 사원 목록 조회
     * status
     * all(전체 사원), employed(재직 사원), retired(퇴사사 사원)
     * defalut 전체, 입사일 desc
     */
    public static function getMemberList($status = '', $order_by = 'mb_hire_date', $direction = 'desc', $paginate = true)
    {
        $query = self::query();
        
        if ($status === 'employed') {
            $query->whereNull('mb_retire_date');
        } 
        elseif ($status === 'retired') {
            $query->whereNotNull('mb_retire_date');
        }

        $query->orderBy($order_by, $direction);

        return $paginate ? $query->paginate(10): $query->get();
    }

    /**
     * 퇴사일 업데이트
     */
    public static function updateRetireDate(array $data): array
    {
        $register_count = 0;
        $update_count = 0;
        $delete_count = 0;

        foreach ($data as $row) {
            $member = self::find($row['id']);
            if (!$member) continue;

            $new_date = $row['retire_date'];
            $old_date = $member->mb_retire_date; 

            // 퇴사일 등록
            if (!$member->mb_retire_date && $new_date) {
                $member->mb_retire_date = $new_date;
                $member->save();
                $register_count++;
                // log 저장
                LogService::member_log([
                    'mb_name' => $member->mb_name,
                    'content' => "퇴사일 등록 / {$new_date}",
                ]);
            }
            // 퇴사일 삭제
            else if ($member->mb_retire_date && !$new_date) {
                $member->mb_retire_date = null;
                $member->save();
                $delete_count++;
                // log 저장
                LogService::member_log([
                    'mb_name' => $member->mb_name,
                    'content' => "퇴사일 삭제 / {$old_date}",
                ]);
            }
            // 퇴사일 수정
            else if ($member->mb_retire_date && $new_date && $member->mb_retire_date != $new_date) {
                $member->mb_retire_date = $new_date;
                $member->save();
                $update_count++;
                // log 저장
                LogService::member_log([
                    'mb_name' => $member->mb_name,
                    'content' => "퇴사일 수정 / {$old_date} → {$new_date}",
                ]);
            }
        }

        return [
            'register_count' => $register_count,
            'update_count'   => $update_count,
            'delete_count'   => $delete_count,
        ];
    }

    /**
     * 사원 추가
     */
    public static function add(array $data)
    {
        $member = self::create([
            'mb_affiliation' => $data['affiliation'],
            'mb_department'  => $data['dept'],
            'mb_name'        => $data['name'],
            'mb_hire_date'   => $data['hire_date'],
            'mb_retire_date' => $data['retire_date'] ?? null,
        ]);

        return $member;
    }
}
