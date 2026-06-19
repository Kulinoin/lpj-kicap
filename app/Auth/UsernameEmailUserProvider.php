<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Support\Arrayable;

class UsernameEmailUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials): ?UserContract
    {
        $credentials = array_filter(
            $credentials,
            fn (string $key): bool => ! str_contains($key, 'password'),
            ARRAY_FILTER_USE_KEY
        );

        if (empty($credentials)) {
            return null;
        }

        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if ($key === 'email' || $key === 'username') {
                $query->where(function ($query) use ($value): void {
                    $query
                        ->where('email', $value)
                        ->orWhere('username', $value);
                });

                continue;
            }

            if ($value instanceof Arrayable || is_array($value)) {
                $query->whereIn($key, $value);

                continue;
            }

            $query->where($key, $value);
        }

        return $query->first();
    }
}
