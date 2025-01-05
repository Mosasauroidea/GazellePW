<?php

namespace Gazelle\Manager;

use Gazelle\Action\BasicReward;
use Gazelle\Action\UserActionEnum;

class ActionTrigger extends \Gazelle\Base {
    private Reward $rewardManager;
    public function __construct() {
        parent::__construct();
        $this->rewardManager = new Reward;
    }
    public function triggerUpload(int $GroupID, int $TorrentID) {
        $reward = BasicReward::getReward(UserActionEnum::UploadTorrent);
        $this->rewardManager->sendReward($reward, [$this->user['ID']], "Basic reward for uploading torrent($TorrentID).", false, true);
    }

    public function triggerTorrentCheck(int $TorrentID) {
        $reward = BasicReward::getReward(UserActionEnum::CheckTorrent);
        $this->rewardManager->sendReward($reward, [$this->user['ID']], "Basic reward for checking torrent($TorrentID).", false, true);
    }

    public function triggerMovieEdit(int $GroupID) {
        $reward = BasicReward::getReward(UserActionEnum::EditMovie);
        $this->rewardManager->sendReward($reward, [$this->user['ID']], "Basic reward for editing movie($GroupID) info.", false, true);
    }

    public function triggerArtistEdit(int $ArtistID) {
        $reward = BasicReward::getReward(UserActionEnum::EditArtist);
        $this->rewardManager->sendReward($reward, [$this->user['ID']], "Basic reward for editing Artist($ArtistID) info.", false, true);
    }

    public function triggerNewRequest(int $RequestID, int $TorrentID) {
        $reward = BasicReward::getReward(UserActionEnum::CreateRequest);
        $this->rewardManager->sendReward($reward, [$this->user['ID']], "Basic reward for Requesting($RequestID) Torrent.", false, true);
    }

    public function triggerFillRequest(int $RequestID, int $TorrentID) {
        $reward = BasicReward::getReward(UserActionEnum::FillRequest);
        $this->rewardManager->sendReward($reward, [$this->user['ID']], "Basic reward for Filling Request($RequestID) by Torrent($TorrentID).", false, true);
    }

    public function triggerCreateCollage(int $CollageID) {
        $reward = BasicReward::getReward(UserActionEnum::CreateCollage);
        $this->rewardManager->sendReward($reward, [$this->user['ID']], "Basic reward for Creating Collage($CollageID).", false, true);
    }

    public function triggerFillCollage(int $CollageID, $GroupID) {
        $reward = BasicReward::getReward(UserActionEnum::FillCollage);
        $this->rewardManager->sendReward($reward, [$this->user['ID']], "Basic reward for Adding Movie($GroupID) to Collage($CollageID).", false, true);
    }

    public function triggerPostComment(int $PostID) {
        $reward = BasicReward::getReward(UserActionEnum::PostComment);
        $this->rewardManager->sendReward($reward, [$this->user['ID']], "Basic reward for Posting($PostID) Comment.", false, true);
    }

    public function triggerCreateTopic(int $TopicID) {
        $reward = BasicReward::getReward(UserActionEnum::CreateTopic);
        $this->rewardManager->sendReward($reward, [$this->user['ID']], "Basic reward for Create Topic($TopicID).", false, true);
    }

    public function triggerInviteeRegister(int $Inviter, int $Invitee) {
        $reward = BasicReward::getReward(UserActionEnum::InviteFriend);
        $this->rewardManager->sendReward($reward, [$Inviter], "Basic reward for invite user($Invitee).", false, true);
    }

    public function triggerReport(string $Type, int $TorrentID, int $ReportID) {
        $reward = BasicReward::getReward(UserActionEnum::Report);
        $this->rewardManager->sendReward($reward, [$this->user['ID']], "Basic reward for report($ReportID) $Type for torrent($TorrentID).", false, true);
    }
}
