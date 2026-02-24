<?php

namespace App\Http\Services\Auth;

use App\Enums\StatusEnum;
use App\Models\UserVerificationCode;
use Carbon\Carbon;

class UserVerifyCodeService
{
    public static function checkExistOtp($userId,$type)
    {
        return UserVerificationCode::where([
            'user_id' => $userId,
            'type' => $type,
        ])->first();
    }
    public static function getOtpCodeData($id)
    {
        return UserVerificationCode::find($id);
    }

    public static function createOtpCode($userId, $code, $type, $validityType = 'day', $validity = 5): UserVerificationCode
    {
        // Save OTP to database
        return UserVerificationCode::create([
            'user_id'    => $userId,
            'code'       => $code,
            'expired_at' => self::calExpireAt($validityType, $validity),
            'type'       => $type,
        ]);
    }

    public static function calExpireAt($validityType='day', $validity=5)
    {
        if ($validityType == 'minute') {
            $expiredAt = now()->addMinutes($validity);
        } elseif ($validityType == 'hour') {
            $expiredAt = now()->addHours($validity);
        } else { // default: day
            $expiredAt = now()->addDays($validity);
        }
        return $expiredAt;
    }

    public static function createUserOtpCode($request,$isResend=null): array
    {
        $otp = null;

        $checkExist = self::checkExistOtp($request->user_id,$request->type);
        // Check if user is blocked
        if ($checkExist && $checkExist->blocked_until) {
            if (now()->lessThan($checkExist->blocked_until)) {
                // Still blocked
                return sendResponse(false, __("You have reached the maximum OTP attempts. Please try again after " . Carbon::parse($checkExist->blocked_until)->diffForHumans()), null, [], 429);
            } else {
                // Block expired → reset attempts
                $checkExist->update([
                    'attempts' => 0,
                    'blocked_until' => null
                ]);
            }
        }

        $validityType = $request->validity_type ?? 'day';
        $validity = $request->validity ?? 5;
        $expiredAt = self::calExpireAt($validityType, $validity);
        if ($checkExist) {
            $otpCode = $checkExist->code;
            if ($isResend) {
                $randomCode = randomNumber(6);
                $checkExist->update([
                    'code' => $randomCode,
                    'expired_at' => $expiredAt
                ]);
            } else {
                $checkExist->update([
                    'expired_at' => $expiredAt
                ]);

            }
            $checkExist->increment('attempts',1);

            $otp = self::getOtpCodeData($checkExist->id);
            if ($otp->attempts > 3) {
                $checkExist->update([
                    'blocked_until' => now()->addMinutes(20) // block for 20 min
                ]);
                return sendResponse(false, __("You have reached the maximum limit of OTP request. Please try again after 20 minutes."),[],429);
            }
            $otp = self::getOtpCodeData($checkExist->id);
        } else {
            $otp = self::createOtpCode($request->user_id, randomNumber(6), $request->type, $validityType, $validity);
        }

        return sendResponse(true, __("Otp created successfully"),$otp);
    }

    public static function checkOpt($userId,$code,$type):mixed
    {
        $verify = UserVerificationCode::where(['user_id' => $userId])
            ->where('code', $code)
            ->where(['type' => $type])
            ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d h:i:s'))
            ->first();
        return $verify;
    }

    public static function otpCodeVerification($userId,$code,$type) {
        $verify = self::checkOpt($userId,$code,$type);
        if($verify) {
            return sendResponse(true, __("OTP verification success"));
        } else {
            return sendResponse(false, __("Invalid OTP code or expired."),[],400);
        }
    }
}
