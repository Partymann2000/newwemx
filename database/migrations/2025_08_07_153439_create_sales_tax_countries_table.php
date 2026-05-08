<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_tax_countries', function (Blueprint $table) {
            $table->id();
            $table->string('country_code')->unique(); // Unique code for the country
            $table->string('sales_tax_name')->default('Sales Tax');
            $table->decimal('sales_tax_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true); // Indicates if the sales tax is active for this country
            $table->timestamps();
        });

        Schema::create('sales_tax_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('sales_tax_countries')->onDelete('cascade');
            $table->string('state_code');
            $table->string('sales_tax_name')->default('Sales Tax');
            $table->decimal('sales_tax_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true); // Indicates if the sales tax is
            $table->timestamps();
        });

        foreach(config('tax.rates') as $country => $rate) {
            \App\Models\SalesTaxCountry::create([
                'country_code' => $country,
                'sales_tax_name' => $rate['sales_tax_name'] ?? 'Sales Tax',
                'sales_tax_rate' => $rate['sales_tax_rate'],
                'is_active' => true,
            ]);

            if($country == 'CA' OR $country == 'US') {
                foreach($rate['states'] as $stateCode => $stateRate) {
                    \App\Models\SalesTaxState::create([
                        'country_id' => \App\Models\SalesTaxCountry::where('country_code', $country)->first()->id,
                        'state_code' => $stateCode,
                        'sales_tax_name' => $stateRate['tax_name'] ?? 'Sales Tax',
                        'sales_tax_rate' => $stateRate['combined_rate'],
                        'is_active' => true,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_tax_countries');
        Schema::dropIfExists('sales_tax_states');
    }
};
