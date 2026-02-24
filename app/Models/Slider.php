<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory, Auditable, HasCustomFields;

    protected $fillable = [
        'photo',
        'position',
        'title',
        'subtitle',
        'offer',
        'published',
        'link',
        'mobile_banner',
        'type',
        'serial',
        'video_link'
    ];

//    public function getRawPhotoAttribute()
//    {
//        return $this->attributes['photo'];
//    }
//    public function getRawMobileBannerAttribute()
//    {
//        return $this->attributes['mobile_banner'];
//    }
//
//    public function getPhotoAttribute($value)
//    {
//        if (empty($value)) return '';
//        return asset('uploads/'.$value);
//    }
//
//    public function getMobileBannerAttribute($value)
//    {
//        if (empty($value)) return '';
//        return asset('uploads/'.$value);
//    }
}
