<?php
/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2019. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Transformers;

use App\Models\Account;
use App\Models\Company;
use App\Models\CompanyToken;
use App\Models\CompanyUser;
use App\Models\User;
use App\Transformers\CompanyTokenTransformer;
use App\Transformers\CompanyTransformer;
use App\Transformers\CompanyUserTransformer;
use App\Utils\Traits\MakesHash;
use Illuminate\Support\Carbon;

class UserTransformer extends EntityTransformer
{
    use MakesHash;

    /**
     * @var array
     */
    protected $defaultIncludes = [
        //'company_users',
        //   'token',
    ];

    /**
     * @var array
     */
    protected $availableIncludes = [
        'companies',
        'company_users',
        'company_user'
    ];


    public function transform(User $user)
    {

        return [
            'id' => $this->encodePrimaryKey($user->id),
            'first_name' => $user->first_name ?: '',
            'last_name' => $user->last_name ?: '',
            'email' => $user->email ?: '',
            'last_login' => Carbon::parse($user->last_login)->timestamp,
            'updated_at' => $user->updated_at,
            'deleted_at' => $user->deleted_at,
            'phone' => $user->phone ?: '',
            'email_verified_at' => $user->getEmailVerifiedAt(),
            'signature' => $user->signature ?: '',
            'custom_value1' => $user->custom_value1 ?: '',
            'custom_value2' => $user->custom_value2 ?: '',
            'custom_value3' => $user->custom_value3 ?: '',
            'custom_value4' => $user->custom_value4 ?: '',
        ];
    }

    public function includeCompanies(User $user)
    {

        $transformer = new CompanyTransformer($this->serializer);

        return $this->includeCollection($user->companies, $transformer, Company::class);

    }

    public function includeToken(User $user)
    {

        $transformer = new CompanyTokenTransformer($this->serializer);

        return $this->includeItem($user->token, $transformer, CompanyToken::class);

    }

    public function includeCompanyTokens(User $user)
    {

        $transformer = new CompanyTokenTransformer($this->serializer);

        return $this->includeCollection($user->tokens, $transformer, CompanyToken::class);

    }

    public function includeCompanyUsers(User $user)
    {

        $transformer = new CompanyUserTransformer($this->serializer);

        return $this->includeCollection($user->company_users, $transformer, CompanyUser::class);

    }

    public function includeCompanyUser(User $user)
    {
        $transformer = new CompanyUserTransformer($this->serializer);

        return $this->includeItem($user->company_user, $transformer, CompanyUser::class);

    }
}