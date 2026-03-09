<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentService extends Model
{
    use HasFactory;

    protected $table = 'agent_services';

    protected $fillable = [
        'reference',
        'user_id',
        'service_id',
        'service_field_id',
        'transaction_id',
        'service_type',
        'field_code',
        'ticket_id',
        'batch_id',
        'request_id',
        'our_id',
        'tracking_id',
        'request_email',
        'email_auth',
        'other_bank',
        'bvn',
        'nin',
        'number',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'dob',
        'email',
        'amount',
        'lga',
        'state',
        'field_name',
        'service_name',
        'service_field_name',
        'bank',
        'description',
        'affidavit',
        'affidavit_file_url',
        'file_url',
        'passport_url',
        'nin_slip_url',
        'cac_file',
        'memart_file',
        'status_report_file',
        'tin_file',
        'field',
        'performed_by',
        'approved_by',
        'completed_by',
        'submission_date',
        'status',
        'comment',
        'company_name',
        'company_type',
        'registration_number',
        'phone_number',
        'city',
        'house_number',
        'street_name',
        'country',
        'from_country',
        'to_country',
        'cac_certificate',
        'departure_date',
        'return_date',
        'trip_type',
        'visa_type',
        'applicant_class',
        
        // Business Address
        'business_state',
        'business_lga',
        'business_city',
        'business_house_number',
        'business_street',
        'business_description',
        
        // Director 2 Information
        'director2_surname',
        'director2_first_name',
        'director2_middle_name',
        'director2_phone',
        'director2_gender',
        'director2_dob',
        'director2_email',
        'director2_address',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'departure_date' => 'date',
        'return_date' => 'date',
        'dob' => 'date',
        'director2_dob' => 'date',
        'field' => 'array', // Cast JSON field to array
    ];

    /** Relationships */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceField()
    {
        return $this->belongsTo(ServiceField::class, 'service_field_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /** Accessors for file URLs */
    public function getNinUrlAttribute()
    {
        $field = $this->field;

        if (is_string($field)) {
            $decoded = json_decode($field, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $field = $decoded;
            }
        } elseif ($field instanceof \Illuminate\Contracts\Support\Arrayable) {
            $field = $field->toArray();
        } elseif (is_object($field)) {
            $field = (array) $field;
        }
        return $field['uploads']['nin'] ?? $this->nin_slip_url ?? null;
    }

    public function getSignatureUrlAttribute()
    {
        $field = $this->field;

        if (is_string($field)) {
            $decoded = json_decode($field, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $field = $decoded;
            }
        } elseif ($field instanceof \Illuminate\Contracts\Support\Arrayable) {
            $field = $field->toArray();
        } elseif (is_object($field)) {
            $field = (array) $field;
        }
        return $field['uploads']['signature'] ?? null;
    }

    public function getPassportUrlAttribute()
    {
        $field = $this->field;

        if (is_string($field)) {
            $decoded = json_decode($field, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $field = $decoded;
            }
        } elseif ($field instanceof \Illuminate\Contracts\Support\Arrayable) {
            $field = $field->toArray();
        } elseif (is_object($field)) {
            $field = (array) $field;
        }
        return $field['uploads']['passport'] ?? $this->passport_url ?? null;
    }

    public function getDirector2NinUrlAttribute()
    {
        $field = $this->field;

        if (is_string($field)) {
            $decoded = json_decode($field, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $field = $decoded;
            }
        } elseif ($field instanceof \Illuminate\Contracts\Support\Arrayable) {
            $field = $field->toArray();
        } elseif (is_object($field)) {
            $field = (array) $field;
        }
        return $field['uploads']['director2_nin'] ?? null;
    }

    public function getDirector2SignatureUrlAttribute()
    {
        $field = $this->field;

        if (is_string($field)) {
            $decoded = json_decode($field, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $field = $decoded;
            }
        } elseif ($field instanceof \Illuminate\Contracts\Support\Arrayable) {
            $field = $field->toArray();
        } elseif (is_object($field)) {
            $field = (array) $field;
        }
        return $field['uploads']['director2_signature'] ?? null;
    }

    public function getDirector2PassportUrlAttribute()
    {
        $field = $this->field;

        if (is_string($field)) {
            $decoded = json_decode($field, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $field = $decoded;
            }
        } elseif ($field instanceof \Illuminate\Contracts\Support\Arrayable) {
            $field = $field->toArray();
        } elseif (is_object($field)) {
            $field = (array) $field;
        }
        return $field['uploads']['director2_passport'] ?? null;
    }

    /** Scopes */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'successful');
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'rejected']);
    }

    public function scopeByService($query, $serviceName)
    {
        return $query->where('service_name', $serviceName);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('submission_date', [$startDate, $endDate]);
    }
}