<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Default permissions to be seeded into the permissions table.
     *
     * @var array
     */
    public array $defaultPermissions = [
        // Dashboard
        'admin.dashboard' => 'View Admin Dashboard',
        'admin.dashboard.statistics' => 'View Statistics on Admin Dashboard such as earnings, orders etc.',
        'admin.dashboard.world_map' => 'View World Map on Admin Dashboard',
        'admin.dashboard.recent_orders' => 'View Recent Orders on Admin Dashboard',
        'admin.dashboard.recent_payments' => 'View Recent Payments on Admin Dashboard',
        'admin.dashboard.online_users' => 'View Online Users on Admin Dashboard',
        'admin.dashboard.application_logs' => 'View Application Logs on Admin Dashboard',

        // Users
        'admin.users' => 'View list of Users',
        'admin.users.create' => 'Create New Users',
        'admin.users.view' => 'View User Details',
        'admin.users.update' => 'Update User Information such as their account details, address, password, sessions, send emails etc...',
        'admin.users.delete' => 'Delete Users',
        'admin.users.impersonate' => 'Impersonate Users',
        'admin.users.manage_roles' => 'Ability to ASSIGN and MANAGE Roles for any users, only give this permission to trusted admins',

        // Payments
        'admin.payments' => 'View list of Payments',
        'admin.payments.view' => 'View details of a Payment',
        'admin.payments.update' => 'Update Payment Information',
        'admin.payments.refund' => 'Refund Payments',
        'admin.payments.complete_manually' => 'Manually mark pending payments as completed',
        'admin.payments.delete' => 'Delete Payments',

        // Orders
        'admin.orders' => 'View list of Orders',
        'admin.orders.view' => 'View details of an Order',
        'admin.orders.update' => 'Update Order Information',
        'admin.orders.perform_actions' => 'Perform actions on Orders such as suspend, unsuspend, terminate, renew etc.',
        'admin.orders.delete' => 'Delete Orders',

        // Misc
        'use-sandbox-gateways' => 'Allows users to checkout using the Sandbox Gateway',
    ];

    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->boolean('super_admin')->default(false);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('permission');
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->string('permission')->unique();
            $table->string('description')->nullable();
        });

        foreach ($this->defaultPermissions as $permission => $description) {
            \DB::table('permissions')->insert([
                'permission' => $permission,
                'description' => $description,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
    }
};
