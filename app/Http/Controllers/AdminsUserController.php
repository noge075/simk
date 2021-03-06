<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests;
use App\Http\Requests\RegisterUserRequest;
use App\User;
use App\Conference;
use App\CountryList;
use Validator;
// use App\Http\Requests;
// TODO: BUAT EDIT USER DARI ADMIN, BUAT EDIT PROFILE DARI USER
class AdminsUserController extends Controller
{
  protected $viewData    = [];
  protected $currentConf = null;

  public function __construct()
  {
    $this->middleware('auth');
    parent::__construct();
    $this->checkAllowed();
  }

  public function showNewUserForm()
  {
    $countryList = new CountryList();
    $this->viewData['countryList'] = $countryList->getList();


    return view('admins.users.new', $this->viewData);
  }

  public function showSingleUser($userId)
  {
    $showUser = User::findOrFail($userId);

    $this->viewData['showUser']    = $showUser;
    $this->viewData['conferences'] = Conference::all();
    $this->viewData['conf'] = Conference::first();

    $countryList = new CountryList();

    $this->viewData['userCountry'] = $countryList->getById($showUser->country);

    return view('admins.users.single', $this->viewData);
  }

  public function showAllUsers()
  {
    $paginator = User::where('deleted_at', '=', NULL)->paginate(10);

    $this->viewData['confs']       = Conference::all();
    $this->viewData['users']       = $paginator;
    $this->viewData['showAction']  = true;
    $this->viewData['showRoute']   = 'admin.user.show';
    $this->viewData['editRoute']   = 'admin.user.edit';
    $this->viewData['deleteRoute'] = 'admin.user.delete';
    // lanjutin nge link ke add, edit , update, delete

    return view('admins.users.all', $this->viewData);
  }

  public function showConferenceUsers(Conference $confUrl)
  {
    $page = 1;
    $per_page = 10;

    if (isset($_GET['page'])) {
      $page = $_GET['page'];
    }


    $reviewers = $confUrl->reviewers;
    $reviewers = $reviewers->toBase();

    $organizers = $confUrl->organizers;
    $organizers = $organizers->toBase();

    $authors   = $confUrl->authors;
    $authors = $authors->toBase();
    //
    $allUsers = $reviewers->merge($organizers)->merge($authors)->unique();

    $paginator = new LengthAwarePaginator($allUsers->forPage($page, $per_page), $allUsers->count(), $per_page, $page);
    $paginator->setPath($confUrl->url);
    // __construct(mixed $items, int $total, int $perPage, int|null $currentPage = null, array $options = array())

    $this->viewData['confs'] = Conference::all();
    $this->viewData['conf'] = $confUrl;
    $this->viewData['users'] = $paginator;
    $this->viewData['showAction'] = true;
    $this->viewData['showRoute'] = 'admin.user.show';
    $this->viewData['editRoute'] = 'admin.user.show';
    $this->viewData['deleteRoute'] = 'admin.user.show';

    return view('admins.users.allperconf', $this->viewData);
  }

  public function refreshUsers(Request $request)
  {
    // dd($confUrl->name);
    // $this->viewData['confs'] = Conference::all();
    //
    return redirect()->route('admin.user.conf', ['confUrl' => $request->url]);
    // return view('admins.users.all', $this->viewData);
  }

  public function registerUser(RegisterUserRequest $request)
  {
    $user = User::create($request->all());

    if ($user) {
      flash()->success('Create New User Success');
    } else{
      flash()->error('Error Occured.');
      return redirect()->back();
    }

    return redirect()->route('admin.user.show', ['userId' => $user->id]);
  }

  public function updateUser(Request $request, $userId)
  {
    $editedUser = User::findOrFail($userId);
    //
    $rules = [
      'salutation' => 'required',
      'first_name' => 'required',
      'last_name' => 'required',
      'status' => 'required',
      'country' => 'required'
    ];
    //
    $userData = $request->all();

    if ($editedUser->email !== $userData['email'] && $userData['email'] !== "") {
      $rules['email'] = 'email|unique:users';
    } else {
      unset($userData['email']);
    }

    if ($userData['password'] === '') {
      unset($userData['password']);
      unset($userData['password_confirmation']);
    } else {
      $rules['password'] = 'required|confirmed';
    }

    $validator = Validator::make($request->all(), $rules);
    //
    if ($validator->fails()) {
      return redirect()
            ->route('admins.users.edit', ['userId' => $editedUser->id])
            ->withErrors($validator)
            ->withInput();
    }

    $countryList = new CountryList();
    $this->viewData['countryList'] = $countryList->getList();

    if (isset($userData['password'])) {
      $userData['password'] = bcrypt($userData['password']);
    }

    $update = $editedUser->update($userData);
    //
    if ($update) {
      flash()->success('Success updating user data');
    }
    //
    $this->viewData['editedUser'] = $editedUser;
    //
    return view('admins.users.single', $this->viewData);
  }

  public function editUser($userId)
  {
    $countryList = new CountryList();
    $this->viewData['countryList'] = $countryList->getList();

    $this->viewData['editedUser'] = User::findOrFail($userId);
    // dd($this->viewData['editedUser']);

    return view('admins.users.edit', $this->viewData);
    // flash()->success('Create New Conference Success');

    //redirect to add to conference
    return redirect()->back();
  }

  public function updateConference(StoreConferenceRequest $request, Conference $confUrl)
  {
    // TODO: edit conference credentials
    // $confUrl->update($request->all());
    // flash()->success('Conferece Succesfully Updated');

    // return redirect()->back();
  }

  protected function checkAllowed() {
    if ($this->user === null || !$this->user->isAdmin()) {
      abort(404);
    }
  }
}
