<?php

namespace App\GraphQL\Queries;

class Accounts
{
    public function __invoke($_, array $args)
    {
        return [
            [
                'id' => 1,
                'nama' => 'Nugraha Ade',
                'email' => 'nugraha@example.com',
                'saldo' => 5000000,
                'status_validasi' => 'verified'
            ],
            [
                'id' => 2,
                'nama' => 'Hebat kali nampak awak yakannn',
                'email' => 'bilek@example.com',
                'saldo' => 7500000,
                'status_validasi' => 'pending'
            ]
        ];
    }
}