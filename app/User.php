<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Conference;
use App\SubmissionService;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'salutation', 'country', 'status', 'address', 'first_name', 'last_name', 'email', 'password', 'activated'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function authoring() {
        return $this->belongsToMany('App\Conference', 'authors', 'user_id', 'conference_id');
    }

    public function reviewing() {
        return $this->belongsToMany('App\Conference', 'reviewers', 'user_id', 'conference_id');
    }

    public function organizing() {
        return $this->belongsToMany('App\Conference', 'organizers', 'user_id', 'conference_id');
    }

    public function participating() {
      return $this->belongsToMany('App\Conference', 'participants', 'user_id', 'conference_id')
        ->withPivot(
            'payment_proof'
          );
    }

    public function participantAppl() {
      return $this->hasMany('App\ParticipantApplication');
    }

    public function getPaymentStatus($confId) {
      $appl =  $this->participantAppl()->conferenceid($confId)->first();
      $conf = Conference::find($confId);

      if ($this->isParticipating($conf)) {
        return 'Registered';
      } else if ($appl->payment_notes != "" && $appl->payment_proof === "") {
        return 'Waiting User Re Upload Payment Proof';
      } else if ($appl->payment_notes === "" && $appl->payment_proof != "") {
        return 'Please Check Payment Proof';
      } else {
        return 'Please Check Payment Proof';
      }
    }

    public function isPaymentProofExist($confId) {
      $appl =  $this->participantAppl()->conferenceid($confId)->first();
      $conf = Conference::find($confId);

      if($appl->payment_proof !== "") {
        return true;
      } else {
        return false;
      }
    }
    // public function isApplyingParticipant(){
    //   // $appl = $this->participantAppl
    //   // if ($this->participantAppl != NULL){
    //   //
    //   // }
    //   //
    //   // return false;
    // }

    public function isAuthoring(Conference $conf)
    {
        return $this->authoring()->whereId($conf->id)->exists();
    }

    public function isActivatedAuthor(Conference $conf)
    {
        return null;
        // return $this->authoring()->where('conference_id', $conf->id)->first()->pivot->activated;
    }

    public function getPayment(Conference $conf)
    {
        return null;
        // return $this->authoring()->where('conference_id', $conf->id)->first()->pivot;
    }

    public function isReviewing(Conference $conf)
    {
        return $this->reviewing()->whereId($conf->id)->exists();
    }

    public function isOrganizing(Conference $conf)
    {
        return $this->organizing()->whereId($conf->id)->exists();
    }

    public function isAdmin()
    {
        return (bool) $this->is_admin;
    }

    public function setAdmin()
    {
        $this->is_admin = 1;
        $this->save();
    }

    public function unsetAdmin()
    {
        $this->is_admin = 0;
        $this->save();
    }

    public function isParticipating(Conference $conf)
    {
        return $this->participating()->whereId($conf->id)->exists();
    }

    public function isRegisteredAuthor(Conference $conf)
    {
        $submissions = $this->submissionsOnConference($conf);
        foreach ($submissions as $submission) {
          $papers = $submission->papers;

          foreach ($papers as $paper) {
            if($paper->status === 'REGISTERED') {
              return true;
            }
          }
        }

        return false;
    }


    //--- PAPER MANAGEMENTS

    public function getAllPaperToReview() {
      return $this->belongsToMany('App\Submission', 'submissions_reviewers', 'user_id', 'submission_id')
      ->withPivot(
          'score_a',
          'score_b',
          'score_c',
          'score_d',
          'score_e',
          'score_f',
          'comments'
        );
    }

    public function getReviewedPaper($paperId) {
      $paper = $this->getAllPaperToReview()->where('submission_id', $paperId)->get()->first();

      return $paper->pivot;
    }

    public function papersToReview() {
      return $this->getAllPaperToReview()->whereNull('score_a')->get();
    }

    public function papersReviewed() {
      return $this->getAllPaperToReview()->whereNotNull('score_a')->get();
    }

    public function isReviewingPaper($submissionId) {
      $submissionId = (int)$submissionId;

      $temp = $this->getAllPaperToReview->whereIn('id', [$submissionId]);

      return !$temp->isEmpty();
    }

    public function submissions() {
        return $this->hasMany('App\Submission');
    }

    public function submissionsOnConference(Conference $conf) {
        return $this->hasMany('App\Submission')->where('conference_id', $conf->id)->get();
    }

    public function getScoresAsAlias($submissionId) {
       if ($this->isReviewingPaper($submissionId)) {
          $submissionService = new SubmissionService;

          $submissionId = (int)$submissionId;

          $paper = $this->getAllPaperToReview->where('id', $submissionId)->first();
          $piv   = $paper->pivot;

          $temp = [
            $submissionService->getScoreAlias($piv->score_a),
            $submissionService->getScoreAlias($piv->score_b),
            $submissionService->getScoreAlias($piv->score_c),
            $submissionService->getScoreAlias($piv->score_d),
            $submissionService->getScoreAlias($piv->score_e),
            $submissionService->getRecommedationAlias($piv->score_f)
           ];

          return $temp;
       } else {
           return NULL;
       }
    }
}
