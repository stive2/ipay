<?php

namespace App\Http\Middleware\Admin;

use App\Providers\Admin\BasicSettingsProvider;
use Closure;
use Illuminate\Http\Request;

class SMSGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $basic_settings = BasicSettingsProvider::get();
        if ($basic_settings->sms_config == null) return back()->withInput()->with(['warning' => [__('You have to configure your system sms first.')]]);
        return $next($request);
    }
}
