<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin-user';
    protected $description = 'Create an admin user with a person record';

    public function handle()
    {
        $name = $this->ask('Nombre completo');
        $firstName = $this->ask('Primer nombre');
        $lastName = $this->ask('Apellido');
        $cui = $this->ask('CUI (13 dígitos)');
        $email = $this->ask('Email');
        $password = $this->secret('Contraseña');
        $passwordConfirmation = $this->secret('Confirmar contraseña');

        if ($password !== $passwordConfirmation) {
            $this->error('Las contraseñas no coinciden');
            return 1;
        }

        // Array de opciones para género con IDs correctos
        $genderOptions = [
            '1' => 'Masculino',
            '2' => 'Femenino',
            '3' => 'Otro'
        ];
        
        // Obtener la selección como texto
        $genderChoice = $this->choice('Género', $genderOptions, '1');
        
        // Obtener el ID correspondiente a la selección
        $genderId = array_search($genderChoice, $genderOptions);

        // Crear la persona primero
        $person = Person::firstOrCreate(
            ['cui' => $cui],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender_id' => $genderId, // Aquí usamos el ID numérico
                'email' => $email,
                'phone' => $this->ask('Teléfono (opcional)', null)
            ]
        );

        // Luego crear el usuario
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'cui' => $cui,
            'role_id' => 1, // Asumiendo que 1 es el rol de administrador
        ]);

        $this->info("Usuario administrador creado con éxito: {$user->email}");
        return 0;
    }
}