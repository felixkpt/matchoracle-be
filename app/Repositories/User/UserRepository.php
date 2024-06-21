<?php

namespace App\Repositories\User;

use App\Mail\SendPassword;
use App\Models\Core\AuthenticationLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class UserRepository implements UserRepositoryInterface
{
    use CommonRepoActions;

    function __construct(protected User $model)
    {
    }

    public function index($id = null)
    {

        $users = $this->model::with(['roles'])
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()))
            ->when($id, fn ($q) => $q->where('id', $id))
            ->when(request()->role_id, function ($q) {
                if (request()->has('negate')) {
                    $q->whereDoesntHave('roles', function ($q) {
                        $q->where('roles.id', request()->role_id);
                    });
                } else {
                    $q->whereHas('roles', function ($q) {
                        $q->where('roles.id', request()->role_id);
                    });
                }
            });

        if ($this->applyFiltersOnly) return $users;

        $uri = '/dashboard/settings/users/';

        $users = SearchRepo::of($users, ['name', 'id'])
            ->addColumn('Roles', function ($user) {
                return implode(', ', $user->roles()->get()->pluck('name')->toArray());
            })
            ->addFillable('password_confirmation', [], 'avatar')
            ->addFillable('roles_multilist', ['input' => 'input', 'type' => 'checkbox'], 'two_factor_enabled')
            ->addFillable('direct_permissions_multilist', ['input' => 'input', 'type' => 'checkbox'], 'roles_multilist')
            ->addFillable('two_factor_enabled', ['input' => 'input', 'type' => 'checkbox'], 'theme')
            ->addFillable('allowed_session_no', ['input' => 'input', 'type' => 'number', 'min' => 1, 'max' => 10], 'theme')
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'getStatus')
            ->addColumn('action', fn ($q) => call_user_func('actionLinks', $q, $uri, 'modal', 'modal'))
            ->htmls(['Status']);

        return response(['results' => $id ? $users->first() : $users->paginate(), 'status' => true]);
    }

    public function create()
    {
        return response(['status' => true, 'results' => null]);
    }

    public function store(Request $request, $data)
    {

        if (!$request->id) {
            $data['password'] = bcrypt($data['password']);
        }

        $user = $this->autoSave($data);

        if (!$user->default_role_id) {
            $user->default_role_id = $request->roles_list[0];
            $user->save();
        }

        if ($request->roles_list) {
            $roles = Role::whereIn('id', $request->roles_list)->get();
            $user->syncRoles($roles);
        }

        if ($request->direct_permissions_list) {
            $permissions = Permission::whereIn('id', $request->direct_permissions_list)->get();
            $user->syncPermissions($permissions);
        }

        return response(['results' => $user, 'message' => 'User ' . ($request->id ? 'updated' : 'created') . ' successfully.']);
    }

    public function show($id)
    {
        return $this->index($id);
    }

    public function edit($id)
    {
        $user = $this->model::with('roles')->findOrFail($id);

        return response(['status' => true, 'results' => SearchRepo::of($user, [], [], ['name', 'email'])]);
    }

    public function profileUpdate(Request $request)
    {

        $user = $this->model::find(auth()->user()->id);
        $request->validate([
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->hasFile('avatar')) {
            $datePath = Carbon::now()->format('Y/m/d');
            $avatarPath = $request->file('avatar')->store('users/' . $datePath);
            $user->avatar = $avatarPath;
        }

        $user->save();

        $user = $this->model::find(auth()->user()->id);
        $user->token = $user->createToken("API TOKEN")->plainTextToken;

        $roles = $user->getRoleNames();
        $user->roles = $roles;
        return response(['type' => 'success', 'results' => $user, 'message' => 'User updated Successfully']);
    }

    public function updateSelfPassword()
    {

        request()->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|max:100|confirmed',
        ]);

        $user = $this->model::find(auth()->user()->id);

        $user->password = Hash::make(request('password'));
        $user->update();
        $password = request('password');

        $data = [
            'subject' => 'New Password For ' . config('app.name'),
            'message' => 'Your ' . config('app.name') . ' new password is a below',
            'password' => $password,
            'instruction' => 'Please use the password as it appears.',
            'user_name' => $user->name,
            'user_email' => $user->email,
        ];

        try {
            Mail::to($user->email)->send(new SendPassword($data));
        } catch (\Exception $e) {

            return response(['type' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response(['type' => 'success', 'message' => 'Password updated Successfully']);
    }

    public function updateOthersPassword()
    {

        request()->validate([
            'password' => 'required|string|min:8|max:100|confirmed',
        ]);

        $user = $this->model::findOrFail(request()->user_id);

        $user->password = Hash::make(request('password'));
        $user->update();
        $password = request('password');

        $data = [
            'subject' => 'New Password For ' . config('app.name'),
            'message' => 'Your ' . config('app.name') . ' new password is a below',
            'password' => $password,
            'instruction' => 'Please use the password as it appears.',
            'user_name' => $user->name,
            'user_email' => $user->email,
        ];
        try {
            Mail::to($user->email)->send(new SendPassword($data));
        } catch (\Exception $e) {

            return response(['type' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response(['type' => 'success', 'message' => 'Password updated Successfully']);
    }

    public function resendToken($userId)
    {

        $user = User::findOrFail($userId);
        request()->merge(['email' => $user->email]);

        request()->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            request()->only('email')
        );

        return response(['message' => Str::title(Str::replace('.', ' ', $status))]);
    }

    public function autoLoginUser($userId)
    {

        $user = $this->model::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->token = $user->createToken("API TOKEN")->plainTextToken;

        $roles = $user->getRoleNames();
        $user->roles = $roles;

        return response()->json([
            'message' => 'Logged In Successfully',
            'results' => $user,
        ], 200);
    }

    public function loginLogs()
    {
        return response(['results' => []]);
    }

    public function listAttemptedLogins()
    {
        $days = \request()->days ?? 30;
        $failedloginattempts = AuthenticationLog::leftjoin('users', 'authentication_log.authenticatable_id', 'users.id')
            ->select(
                'authentication_log.id',
                'authentication_log.authenticatable_type',
                'authentication_log.ip_address',
                'authentication_log.user_agent',
                'authentication_log.login_at as time_of_access',
                'authentication_log.logout_at',
                'authentication_log.login_successful as successful',
                'users.name as user'
            )->where('login_successful', '=', 0)->whereDate('authentication_log.login_at', '>=', Carbon::today()->subDays($days));



        if (\request()->tabs) {
            return [
                'failed_login_attempts' => $failedloginattempts->count()
            ];
        }

        return SearchRepo::of($failedloginattempts)
            ->addColumn('login_successful', function ($failedloginattempts) {
                if ($failedloginattempts->login_successful) {
                    $color = 'success';
                } else {
                    $color = 'danger';
                }
                return $color;
            })
            ->paginate();
    }
}
