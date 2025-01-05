<?

namespace Gazelle\Action;

abstract class UserActionEnum {
    const UploadTorrent = "UploadTorrent";
    const CheckTorrent = "CheckTorrent";
    const Report = "Report";
    const CreateRequest = "CreateRequest";
    const FillRequest = "FillRequest";
    const CreateCollage = "CreateCollage";
    const FillCollage = "FillCollage";
    const EditMovie = "EditMovie";
    const EditArtist = "EditArtist";
    const PostComment = "PostComment";
    const CreateTopic = "CreateTopic";
    const InviteFriend = "InviteFriend"; // need register success
}
