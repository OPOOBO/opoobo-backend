<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
        ]);

        // Remove modules no longer part of the ecosystem
        Module::whereNotIn('name', ['bus', 'market', 'go', 'mall'])->delete();

        // Seed modules
        Module::updateOrCreate(
            ['name' => 'bus'],
            [
                'display_name' => 'OPOOBO Bus',
                'description' => 'Book interstate and intercity bus trips across Nigeria. Compare operators, pick your seat, and pay securely — all in one place.',
                'api_base_url' => 'https://tapp.bus.opoobo.com/api',
                'icon' => 'directions_bus_rounded',
                'website_url' => 'https://bus.opoobo.com',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'check_endpoint' => '/check_user.php',
                'create_endpoint' => '/reg_user.php',
                'change_password_endpoint' => '/change_password_opoobo_one.php',
                'required_fields' => ['mobile', 'ccode'],
            ]
        );

        Module::updateOrCreate(
            ['name' => 'market'],
            [
                'display_name' => 'OPOOBO Market',
                'description' => 'Buy and sell everything from trusted vendors. Discover deals, order delivery, and pay securely — your one-stop online marketplace.',
                'api_base_url' => null,
                'icon' => 'shopping_cart_rounded',
                'website_url' => 'https://opoobo.market',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'check_endpoint' => null,
                'create_endpoint' => null,
                'change_password_endpoint' => null,
                'required_fields' => [],
            ]
        );

        Module::updateOrCreate(
            ['name' => 'go'],
            [
                'display_name' => 'OPOOBO Go',
                'description' => 'Book rides instantly by cab or ride-hailing. Track your driver in real time, pay seamlessly, and move smarter with OPOOBO Go.',
                'api_base_url' => null,
                'icon' => 'directions_car_rounded',
                'website_url' => 'https://go.opoobo.com',
                'is_active' => false,
                'is_featured' => false,
                'sort_order' => 3,
                'check_endpoint' => null,
                'create_endpoint' => null,
                'change_password_endpoint' => null,
                'required_fields' => [],
            ]
        );

        Module::updateOrCreate(
            ['name' => 'mall'],
            [
                'display_name' => 'OPOOBO Mall',
                'description' => 'Explore a curated mall of top brands and shops. Find everything you need in one place, with easy checkout and doorstep delivery.',
                'api_base_url' => null,
                'icon' => 'storefront_rounded',
                'website_url' => 'https://opoobo.com',
                // First WebView mini-app (Phase 1 proof). The URL is a
                // placeholder until the real Mall web app exists — swap
                // module_url then, no app update needed.
                'module_url' => 'https://opoobo.com',
                'version' => '1.0.0',
                'permissions' => [],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
                'check_endpoint' => null,
                'create_endpoint' => null,
                'change_password_endpoint' => null,
                'required_fields' => [],
            ]
        );
    }
}
