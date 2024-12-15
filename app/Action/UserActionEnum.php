<?

namespace Gazelle\Action;

abstract class UserActionEnum {
    const UploadTorrent = "upload_torrent";
    const CheckTorrent = "check_torrent";
    const Report = "report_torrent";
    const CreateRequest = "create_request";
    const FillRequest = "fill_request";
    const CreateCollage = "create_collage";
    const FillCollage = "fill_collage";
    const EditMovie = "edit_movie";
    const EditArtist = "edit_artist";
    const PostComment = "post_comment";
    const CreateTopic = "create_topic";
    const InviteFriend = "invite_friend"; // need register success
}
