<?php

namespace App\Models\BackgroundCheck;

use App\Models\BaseModel;
use App\Models\BackgroundCheck\BackgroundCheckScope;

/**
 * Class User.
 */
class BackgroundCheckModel extends BaseModel 
{
use BackgroundCheckScope;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'temp_session_id',
        'order_id',
        'current_step',
        'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.online_background_checks_table');
    }

   

   
}
