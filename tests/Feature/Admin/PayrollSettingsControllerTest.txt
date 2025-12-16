<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\PayrollSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function admin_can_view_payroll_settings_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create(['admin_id' => $admin->id]);
        
        $response = $this->actingAs($admin)
            ->get(route('admin.payroll.settings.edit'));
            
        $response->assertStatus(200);
        $response->assertViewIs('admin.payroll.settings.edit');
    }

    /** @test */
    public function admin_can_update_payroll_settings()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create(['admin_id' => $admin->id]);
        
        $response = $this->actingAs($admin)
            ->put(route('admin.payroll.settings.update'), [
                'days_in_month' => 26,
                'enable_halfday_deduction' => '1',
                'enable_reimbursement' => '1',
                'deductible_leave_type_ids' => [1, 2, 3],
            ]);
            
        $response->assertRedirect(route('admin.payroll.settings.edit'));
        $this->assertDatabaseHas('payroll_settings', [
            'company_id' => $company->id,
            'days_in_month' => 26,
            'enable_halfday_deduction' => true,
            'enable_reimbursement' => true,
        ]);
    }

    /** @test */
    public function half_day_deduction_toggle_works()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create(['admin_id' => $admin->id]);
        
        // First, enable half-day deduction
        $this->actingAs($admin)
            ->put(route('admin.payroll.settings.update'), [
                'days_in_month' => 30,
                'enable_halfday_deduction' => '1',
                'enable_reimbursement' => '1',
            ]);
            
        $this->assertDatabaseHas('payroll_settings', [
            'company_id' => $company->id,
            'enable_halfday_deduction' => true,
        ]);
        
        // Then disable it
        $this->actingAs($admin)
            ->put(route('admin.payroll.settings.update'), [
                'days_in_month' => 30,
                'enable_reimbursement' => '1',
                // Don't send enable_halfday_deduction to simulate unchecked checkbox
            ]);
            
        $this->assertDatabaseHas('payroll_settings', [
            'company_id' => $company->id,
            'enable_halfday_deduction' => false,
        ]);
    }

    /** @test */
    public function reimbursement_toggle_works()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create(['admin_id' => $admin->id]);
        
        // First, enable reimbursement
        $this->actingAs($admin)
            ->put(route('admin.payroll.settings.update'), [
                'days_in_month' => 30,
                'enable_halfday_deduction' => '1',
                'enable_reimbursement' => '1',
            ]);
            
        $this->assertDatabaseHas('payroll_settings', [
            'company_id' => $company->id,
            'enable_reimbursement' => true,
        ]);
        
        // Then disable it
        $this->actingAs($admin)
            ->put(route('admin.payroll.settings.update'), [
                'days_in_month' => 30,
                'enable_halfday_deduction' => '1',
                // Don't send enable_reimbursement to simulate unchecked checkbox
            ]);
            
        $this->assertDatabaseHas('payroll_settings', [
            'company_id' => $company->id,
            'enable_reimbursement' => false,
        ]);
    }

    /** @test */
    public function default_values_are_used_when_no_settings_exist()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create(['admin_id' => $admin->id]);
        
        // Delete any existing settings
        PayrollSetting::where('company_id', $company->id)->delete();
        
        $response = $this->actingAs($admin)
            ->get(route('admin.payroll.settings.edit'));
            
        $response->assertStatus(200);
        $response->assertViewHas('settings', function($settings) {
            return $settings->enable_halfday_deduction === true && 
                   $settings->enable_reimbursement === true &&
                   $settings->days_in_month === 30;
        });
    }
}
