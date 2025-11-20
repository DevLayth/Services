<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Customers extends Component
{
    public $customers = [];
    public $name;
    public $phone;
    public $customerId;
    public $customerServices = [];
    public $allServices = [];
    public $selectedServiceId;
    public $editMode = false;

    public $messages = [
        'add.success'    => 'The new customer has been added successfully.',
        'add.error'      => 'There was an error adding the new customer.',
        'update.success' => 'The customer has been updated successfully.',
        'update.error'   => 'There was an error updating the customer.',
        'delete.success' => 'The customer has been deleted successfully.',
        'delete.error'   => 'There was an error deleting the customer.',

        'service.add.success' => 'The service has been added to the customer successfully.',
        'service.add.error'   => 'There was an error adding the service to the customer.',
    ];

    public $message;
    public $messageType;
    public $showMessage = false;



    public function mount()
    {
        $this->fetchCustomers();
    }

    public function render()
    {
        return view('livewire.customers');
    }


    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    private function alert($key, $type = 'success')
    {
        $this->message = $this->messages[$key] ?? '';
        $this->messageType = $type;
        $this->showMessage = true;
    }

    public function resetInput()
    {
        $this->resetValidation();
        $this->reset(
            'name',
            'phone',
            'customerId',
        );
        $this->editMode = false;
    }

    public function fetchCustomers()
    {
        $this->customers = DB::table('customers')->orderBy('id')->get();
        $this->allServices = DB::table('services')->orderBy('id')->get();

    }


    // ---------------------------------------------------------
    // Create
    // ---------------------------------------------------------

    public function addCustomer()
    {
        $this->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
        ]);

        try {
            DB::table('customers')->insert([
                'name'       => $this->name,
                'phone'      => $this->phone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->resetInput();
            $this->resetValidation();
            $this->alert('add.success', 'success');
            $this->fetchCustomers();
        } catch (\Exception $e) {
            $this->alert('add.error', 'danger');
        }
    }


    // ---------------------------------------------------------
    // Edit / Update
    // ---------------------------------------------------------

    public function editCustomer($id)
    {
        $customer = DB::table('customers')->find($id);

        if (!$customer) {
            $this->alert('update.error', 'danger');
            return;
        }

        $this->resetValidation();
        $this->customerId = $id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->editMode = true;
        $this->showMessage = false;
    }



    public function updateCustomer()
    {
        $this->resetValidation();
        $this->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $this->customerId,
        ]);

        try {
            DB::table('customers')->where('id', $this->customerId)->update([
                'name'       => $this->name,
                'phone'      => $this->phone,
                'updated_at' => now(),
            ]);

            $this->alert('update.success', 'success');
            $this->resetInput();
            $this->editMode = false;
            $this->fetchCustomers();
        } catch (\Exception $e) {
            $this->alert('update.error', 'danger');
        }
    }


    // ---------------------------------------------------------
    // Delete
    // ---------------------------------------------------------

    public function setCustomerId($id, $name)
    {
        $this->reset([
            'customerId',
            'name',
        ]);
        $this->customerId = $id;
        $this->name = $name;
    }

    public function deleteCustomer()
    {
        try {
            DB::table('customers')->where('id', $this->customerId)->delete();

            $this->alert('delete.success', 'success');
            $this->resetInput();
            $this->fetchCustomers();
        } catch (\Exception $e) {
            $this->alert('delete.error', 'danger');
        }
    }

    // ---------------------------------------------------------
    // Manage Services for Customer
    // ---------------------------------------------------------
    public function manageServices($id, $name)
    {
        $this->resetInput();
        $this->customerId = $id;
        $this->name = $name;
        $this->fetchServicesForCustomer($id);
    }

public function fetchServicesForCustomer($customerId)
{
    try {
        $this->customerServices = DB::table('subscriptions')
            ->join('customers', 'customers.id', '=', 'subscriptions.customer_id')
            ->join('services', 'services.id', '=', 'subscriptions.service_id')
            ->where('customers.id', $customerId)
            ->select('*', 'services.*')
            ->get();
    } catch (\Exception $e) {
        $this->customerServices = [];
    }
}

public function confirmAddService()
{
    $this->validate([
        'selectedServiceId' => 'required|exists:services,id',
    ]);

    $price = DB::table('services')->where('id', $this->selectedServiceId)->value('price') ?? 0;

    try {
        DB::table('subscriptions')->insert([
            'customer_id' => $this->customerId,
            'service_id'  => $this->selectedServiceId,
            'price'       => $price,
            'currency_id'  => DB::table('services')->where('id', $this->selectedServiceId)->value('currency_id') ?? null,
            'start_date'  => now(),
            'next_payment_date'  => now()->addMonth(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $this->fetchServicesForCustomer($this->customerId);
        $this->alert('service.add.success', 'success');
    } catch (\Exception $e) {
        $this->alert('service.add.error', 'danger');
    }
    $this->resetInput();
    $this->fetchServicesForCustomer($this->customerId);
}

}
