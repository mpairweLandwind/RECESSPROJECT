<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'date_of_birth' => ['required', 'date'],
            'school_reg_no' => ['nullable', 'string', 'max:255'],
            'password' => $this->passwordRules(),
            'profile_photo' => ['nullable', 'image', 'max:1024'], // Adjust max size as needed
            'role' => ['required', 'in:admin,representative,participant'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        // Handle the profile photo upload
        $profilePhotoPath = null;
        if (isset($input['profile_photo'])) {
            $profilePhotoPath = $input['profile_photo']->store('profile_photos', 'public');
        }


        return User::create([
            'username' => $input['username'],
            'firstname' => $input['firstname'],
            'lastname' => $input['lastname'],
            'email' => $input['email'],
            'date_of_birth' => $input['date_of_birth'],
            'school_reg_no' => $input['school_reg_no'],
            'password' => Hash::make($input['password']),
            'profile_photo' => $profilePhotoPath,
            'role' => $input['role'],
        ]);
    }
}
