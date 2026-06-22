<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnualLeaveUse extends Model
{
	protected $table = 'annual_leave_use';
	protected $primaryKey = 'alu_id';
	protected $fillable = [
		'mb_id', 'al_id', 'alu_date', 'alu_reason', 'alu_status'
	];

	const STATUS_PENDING  = 'pending';
	const STATUS_APPROVED = 'approved';
	const STATUS_REJECTED = 'rejected';
}
