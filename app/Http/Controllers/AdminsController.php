<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\StoreConferenceRequest;
use App\User;
use App\Conference;
use App\ConferenceDate;
use App\ReviewQuestion;
use App\ConferenceService;
use App\Http\Controllers\OrgHomeController;
use Validator;
use Carbon\Carbon;

// use App\Http\Requests;

class AdminsController extends Controller
{
  protected $viewData = [];

  public function __construct()
  {
    $this->middleware('auth');
    parent::__construct();
    $this->checkAllowed();
  }

  public function index()
  {
    return view('admins.dashboard');
  }

  public function showNewConferenceForm()
  {
    $dateNow = Carbon::now();
    $dateNow->addMonth();
    $dateNow = $dateNow->toDateString();

    // autofill avoid empty database
    $this->viewData['edited']['start_conference'] = $dateNow;
    $this->viewData['edited']['end_conference']   = $dateNow;
    $this->viewData['edited']['submission_deadline'] = $dateNow;
    $this->viewData['edited']['acceptance'] = $dateNow;
    $this->viewData['edited']['camera_ready'] = $dateNow;
    $this->viewData['edited']['registration'] = $dateNow;

    return view('admins.conferences.new', $this->viewData);
  }

  public function editConference(Conference $confUrl)
  {
    $this->viewData['conf'] = $confUrl;
    $this->viewData['edited'] = $confUrl->toArray();

    return view('admins.conferences.edit', $this->viewData);
  }
  public function showSingleConference(Conference $confUrl)
  {
    $visibleDates = $confUrl->getVisibleArray();

    $this->viewData['conf']  = $confUrl;
    $this->viewData['dates'] = $visibleDates;
    $this->viewData['boldNum'] = count($visibleDates['submission_deadline']);
    $this->viewData['startDate'] = $visibleDates->get('start_conference');

    return view('admins.conferences.single', $this->viewData);
  }

  public function showAllConferences()
  {
    // FIXME add pagination for all conferences
    $this->viewData['confs'] = Conference::all();

    return view('admins.conferences.all', $this->viewData);
  }

  public function storeNewConference(StoreConferenceRequest $request)
  {
    $conf = Conference::create($request->all());
    $confDate = ConferenceDate::create($request->all());
    $ReviewQuestion = ReviewQuestion::create(['conference_id' => $conf->id]);
    $website = Website::create(['conference_id' => $conf->id]);
    
    $conf->dates()->save($confDate);


    flash()->success('Create New Conference Success');

    return redirect()->route('admin.conf.show', $conf->url);
  }

  public function updateConference(Conference $confUrl, Request $request)
  {
    $service = new ConferenceService();
    $update  = $service->update($confUrl, $request);

    if ($update) {
      flash()->success('Update Conference Success');
      return redirect()->route('admin.conf.show', $confUrl->url);
    } else {
      return redirect()->back()->withErrors($update);
    }
  }

  public function showExtendConference(Conference $confUrl)
  {
      $this->viewData['conf']  = $confUrl;
      $this->viewData['dates'] = $confUrl->dates;

      $dateNow = Carbon::now();
      $dateNow->addMonth();
      $dateNow = $dateNow->toDateString();

      // autofill avoid empty database
      $this->viewData['edited']['start_conference'] = $dateNow;
      $this->viewData['edited']['end_conference']   = $dateNow;
      $this->viewData['edited']['submission_deadline'] = $dateNow;
      $this->viewData['edited']['acceptance'] = $dateNow;
      $this->viewData['edited']['camera_ready'] = $dateNow;
      $this->viewData['edited']['registration'] = $dateNow;

      return view('admins.conferences.extends', $this->viewData);
  }

  public function postExtends(Conference $confUrl, Request $request)
  {
    $service = new ConferenceService();
    $result  = $service->postExtends($confUrl, $request);

    if ($result) {
      flash()->success('Add New Date Success!');
      return redirect()->route('admin.conf.extends', $confUrl->url);
    } else {
      return redirect()->back()->withErrors($result);
    }
  }

  public function updateVisibility(Conference $confUrl, Request $request)
  {
    $service = new ConferenceService();
    $result  = $service->updateVisibility($confUrl, $request);

    if ($result === true) {
      flash()->success('Update Visibility Success');
      return redirect()->route('admin.conf.extends', $confUrl->url);
    } else {
      return redirect()->back()->withErrors($result);
    }
  }

  protected function checkAllowed() {
    if ($this->user === null || !$this->user->isAdmin()) {
      abort(404);
    }
  }
}
