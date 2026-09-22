<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Emails
    |--------------------------------------------------------------------------
    | Users with these emails can access the admin dashboard.
    */
    'admin_emails' => array_filter(explode(',', env('MINIAPP_ADMIN_EMAILS', 'admin@opoobo.com'))),
];
