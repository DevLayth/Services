<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Services extends Component
{
    public $services;
    public $name;
    public $price;
    public $currencyId;
    public $currencies = [];
    public $description;
    public $editMode = false;
    public $serviceId;
    public $showMessage = false;
    public $message;
    public $messageType;
    public $messages = [
        'add.success'    => 'The new service has been added successfully.',
        'add.error'      => 'There was an error adding the new service.',
        'update.success' => 'The service has been updated successfully.',
        'update.error'   => 'There was an error updating the service.',
        'delete.success' => 'The service has been deleted successfully.',
        'delete.error'   => 'There was an error deleting the service.',
    ];


    public function render()
    {
        return view('livewire.services');
    }

    public function mount()
    {
        $this->fetchServices();
        $this->fetchCurrencies();
    }
    public function fetchServices()
    {
        $this->services = DB::table('services')->get()->toArray();
    }

    public function fetchCurrencies()
    {
        try {
            $this->currencies = DB::table('currencies')->get()->toArray();
        } catch (\Exception $e) {
            $this->currencies = [];
        }
    }


    public function alert($key, $type = 'success')
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
            'price',
            'description',
            'serviceId',
            'currencyId',


        );
        $this->editMode = false;
    }


    // ---------------------------------------------------------
    // Create
    // ---------------------------------------------------------
    public function addService()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'currencyId' => 'required|exists:currencies,id',
        ]);

        try {
            DB::table('services')->insert([
                'name' => $this->name,
                'price' => $this->price,
                'description' => $this->description,
                'currency_id' => $this->currencyId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->resetInput();
            $this->resetValidation();
            $this->alert('add.success', 'success');
            $this->fetchServices();
        } catch (\Exception $e) {
            $this->alert('add.error', 'danger');
        }
    }

    // ---------------------------------------------------------
    // Edit / Update
    // ---------------------------------------------------------
    public function editService($id)
    {
        $service = DB::table('services')->where('id', $id)->first();

        if ($service) {
            $this->serviceId = $service->id;
            $this->name = $service->name;
            $this->price = $service->price;
            $this->description = $service->description;
            $this->editMode = true;
        }
    }
    public function updateService()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'currencyId' => 'nullable|exists:currencies,id',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            DB::table('services')->where('id', $this->serviceId)->update([
                'name' => $this->name,
                'price' => $this->price,
                'currency_id' => $this->currencyId,
                'description' => $this->description,
            ]);
            $this->resetInput();
            $this->alert('update.success', 'success');
            $this->fetchServices();
        } catch (\Exception $e) {
            $this->alert('update.error', 'danger');
        }
    }


    // ---------------------------------------------------------
    // Delete
    // ---------------------------------------------------------
    public function setServiceId($id, $name,$currencyId)
    {
        $this->reset([
            'serviceId',
            'name',
            'currencyId',
        ]);

        $this->serviceId = $id;
        $this->name = $name;
        $this->currencyId = $currencyId;
    }
    public function deleteService()
    {
        try {
            DB::table('services')->where('id', $this->serviceId)->delete();
            $this->alert('delete.success', 'success');
            $this->fetchServices();
        } catch (\Exception $e) {
            $this->alert('delete.error', 'danger');
            $this->fetchServices();
        }
    }
}
