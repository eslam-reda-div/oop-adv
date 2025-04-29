<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use App\Models\Driver;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateDriver extends CreateRecord
{
    protected static string $resource = DriverResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = DB::insert("
            INSERT INTO drivers (
                name, license_number, phone, email, address,
                date_of_birth, license_expiry_date, years_of_experience,
                emergency_contact_name, emergency_contact_phone, status,
                user_id, notes, image_path, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", [
            $data['name'],
            $data['license_number'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['date_of_birth'],
            $data['license_expiry_date'],
            $data['years_of_experience'],
            $data['emergency_contact_name'],
            $data['emergency_contact_phone'],
            $data['status'],
            $data['user_id'],
            $data['notes'],
            $data['image_path']
        ]);

        return new Driver($data);
    }

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            $this->callHook('beforeCreate');

            $this->record = $this->handleRecordCreation($data);

            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        $this->rememberData();

        $this->getCreatedNotification()?->send();

        if ($another) {
            // Ensure that the form record is anonymized so that relationships aren't loaded.
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        $id = DB::select("SELECT LAST_INSERT_ID() AS id")[0]->id;

        $redirectUrl = DriverResource::getUrl('edit', ['record' => $id, ...$this->getRedirectUrlParameters()]);

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode($redirectUrl));
    }


}
