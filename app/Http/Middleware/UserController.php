<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Warehouse;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Notifications\NewUserPasswordCreate;
use App\Services\ChangeLoggerService;
use App\Services\ChangeLogService;
use App\Services\DataTable\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $dataTable = (new DataTable(
            User::select(User::$defaultSelectFields)
        ))
            ->setColumn('id', '#', true, true)
            ->setColumn('name', 'Name', true, true)
            ->setColumn('email', 'Email', true, true)
            ->setColumn('warehouse', 'Warehouse', true, true)
            ->setColumn('created_at', 'Created', true, true)
            ->setColumn('action', 'Action')
            ->setDateColumn('created_at', 'dd.mm.YYYY H:i')
            ->setEnumColumn('warehouse', Warehouse::class)
            ->run();

        return Inertia::render('Users/Index', compact('dataTable'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Users/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreUserRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $validatedRequest = $request->validated();

            $user = new User();
            $user->fill($validatedRequest);
            $user->password = Hash::make(Str::random(15));
            $user->creator_id = auth()->id();
            $user->save();

            $token = app('auth.password.broker')->createToken($user);
            Notification::send($user, new NewUserPasswordCreate($token));

            DB::commit();

            return redirect()->route('users.edit', ['user' => $user->id])->with('success', 'The record has been successfully created.');
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors(['Error creating record.']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     */
    public function show(string $id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  User     $user
     * @return Response
     */
    public function edit(User $user): Response
    {
        $user->load(['changeLogsLimited']);

        return Inertia::render('Users/Edit', [
            'user'       => $user,
            'changeLogs' => Inertia::lazy(fn () => ChangeLogService::getChangeLogsDataTable($user)),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateUserRequest $request
     * @param  User              $user
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        DB::beginTransaction();

        try {
            $changeLoggerService = new ChangeLoggerService($user);

            $user->update($request->validated());

            $changeLoggerService->logChanges($user);

            DB::commit();

            return back()->with('success', 'The record has been successfully updated.');
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors(['Error updating record.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     */
    public function destroy(string $id)
    {
    }
}
