<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Kodeine\Acl\Traits\HasRole;
use App\Repositories\CompanyRepositoryEloquent;
use App\Repositories\TypeRepositoryEloquent;
use App\Repositories\ContactRepositoryEloquent;
use App\Repositories\ModelRepositoryEloquent;
use App\Repositories\VehicleRepositoryEloquent;

class User extends BaseModel implements Transformable, AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, HasRole, SoftDeletes, TransformableTrait;
    
    protected $fillable = ['id', 'company_id', 'contact_id', 'pending_company_id',
                            'name', 'email', 'language','created_at','updated_at'];
    protected $hidden = ['remember_token', 'password'];

    public function contact()
    {
        return $this->belongsTo("App\Entities\Contact");
    }

    public function company()
    {
        return $this->belongsTo("App\Entities\Company");
    }
    
    public function setUp(
        CompanyRepositoryEloquent $companyRepo,
        TypeRepositoryEloquent $typeRepo,
        ContactRepositoryEloquent $contactRepo,
        ModelRepositoryEloquent $modelRepo,
        VehicleRepositoryEloquent $vehicleRepo
    ) {
    
        $company = $companyRepo->create(array('name' => $this->name . ' Inc.', 'api_token' => '123'));
    
        $this->company_id = $company->id;
        $this->save();
        
        $typeRepo->create(array('entity_key' => 'entry',
            'name' => 'repair',
            'company_id' => $company->id));
    
        $typeRepo->create(array('entity_key' => 'entry',
            'name' => 'service',
            'company_id' => $company->id));
    
        $typeCar = $typeRepo->create(array('entity_key' => 'vehicle',
            'name' => 'car',
            'company_id' => $company->id));
    
        $typeTruck = $typeRepo->create(array('entity_key' => 'vehicle',
            'name' => 'truck',
            'company_id' => $company->id));
    
        $typeVendor = $typeRepo->create(array('entity_key' => 'contact',
            'name' => 'vendor',
            'company_id' => $company->id));
    
        $typeDriver = $typeRepo->create(array('entity_key' => 'contact',
            'name' => 'driver',
            'company_id' => $company->id));
    
        $typeRepo->create(array('entity_key' => 'trip',
            'name' => 'tour',
            'company_id' => $company->id));
    
        $typeRepo->create(array('entity_key' => 'trip',
            'name' => 'delivery',
            'company_id' => $company->id));
    
        $contactVendor = $contactRepo->create(array('company_id' => $company->id,
            'contact_type_id' => $typeVendor->id,
            'name' => 'Generic Vendor',
            'license_no' => '123456'));
    
        $contactRepo->create(array('company_id' => $company->id,
            'contact_type_id' => $typeDriver->id,
            'name' => 'Generic Driver',
            'license_no' => '123456'));
    
        $modelCar = $modelRepo->create(array('model_type_id' => $typeCar->id,
            'vendor_id' => $contactVendor->id,
            'name' => 'Generic Car',
            'company_id' => $company->id));
    
        $modelRepo->create(array('model_type_id' => $typeTruck->id,
            'vendor_id' => $contactVendor->id,
            'name' => 'Generic Truck',
            'company_id' => $company->id));
    
        $vehicleRepo->create(array('model_vehicle_id' => $modelCar->id,
            'number' => 'IOP-1234',
            'initial_miliage' => 123,
            'actual_miliage' => 123,
            'cost' => 50000,
            'description' => 'Generic Vehicle',
            'company_id' => $company->id));
    }
}
