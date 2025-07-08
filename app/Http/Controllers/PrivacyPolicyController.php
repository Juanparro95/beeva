<?php

namespace App\Http\Controllers;

class PrivacyPolicyController extends Controller
{
    public function index()
    {
        $settings     = get_settings_by_group('privacy-policy');
        $announcement = get_settings_by_group('announcement');

        return view('privacy-policy', [
            'title'      => $settings->title   ?? 'Privacy Policy',
            'content'    => $settings->content ?? '',
            'updated_at' => $settings->updated_at ? format_date_time($settings->updated_at) : null,

        ], compact('announcement'));
    }
}
