<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogHistory extends Model
{
	protected $table = 'log_history';
	protected $primaryKey = 'log_id';
	public $timestamps = false;

	protected $fillable = [
		'log_mb_name',
		'log_type',
		'log_ref_date',
		'log_reason',
		'log_content',
		'log_date',
	];

	public static function getLogList(int $perPage = 10, int $page = 1, ?string $type = null, ?string $name = null, ?string $startDate = null, ?string $endDate = null): array
	{
		$query = self::orderBy('log_id', 'desc');

		if ($type) {
			$query->where('log_type', $type);
		}
		if ($name) {
			$query->where('log_mb_name', 'like', "%{$name}%");
		}
		if ($startDate) {
			$query->where('log_date', '>=', $startDate);
		}
		if ($endDate) {
			$query->where('log_date', '<=', $endDate);
		}

		$logs = $query->paginate($perPage, ['*'], 'page', $page);

		return [
			'log' => $logs->map(function ($log) {
				return [
					'id'       => $log->log_id,
					'name'     => $log->log_mb_name,
					'type'     => $log->log_type,
					'ref_date' => $log->log_ref_date,
					'reason'   => $log->log_reason,
					'date'     => $log->log_date
						? \Carbon\Carbon::parse($log->log_date)->format('Y-m-d H:i:s')
						: null,
				];
			}),
			'pagination' => [
				'current_page' => $logs->currentPage(),
				'last_page'    => $logs->lastPage(),
				'per_page'     => $logs->perPage(),
				'total'        => $logs->total(),
			],
		];
	}
}
