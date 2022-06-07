<?php
$site_name = SITE_NAME;
$staffpm = "<a href='staffpm.php'>Staff PM</a>";
$lang_rules = array(
    'rules' => "Rules",
    'type' => "Category",
    'info' => "Additional Information",
    'golden_rules' => "Golden Rules",
    'golden_rules_used' => "The Golden Rules encompass all of ${site_name} and the ${site_name} IRC network. These rules are paramount; non-compliance will jeopardize your account.",
    'short_11' => "One account per person, per lifetime.",
    'long_11' => "Users are allowed one account per lifetime. If your account is disabled, contact staff in ${disabled_channel} on ${irc}. Never make another account, you will be disabled without question.",
    'short_12' => "Do not trade, sell, give away, or offer accounts.",
    'long_12' => "If you no longer wish to use your account, send a ${staffpm} and request that your account be disabled.",
    'short_13' => "Do not share accounts.",
    'long_13' => "Accounts are for personal use only. Granting access to your account in any way (e.g., shared login details, external programs) is prohibited. <a href=\"wiki.php?action=article&name=invite\">Invite</a> friends or direct them to our <a href=\"" . TG_GROUP . "\" target='_blank'>official Telegram group</a>.",
    'short_14' => "Do not let your account become inactive.",
    'long_14' => "You agree to log into the site regularly in order to keep your account in good standing. Failure to do so will result in your account being disabled. See <a href=\"wiki.php?action=article&id=11\">Account Inactivity</a> for more information.",
    'short_21' => "Do not invite bad users.",
    'long_21' => "You are responsible for your invitees. You will not be punished if your invitees fail to maintain required share ratios, but invitees who break golden rules will place your invite privileges and account at risk.",
    'short_22' => "Do not trade, sell, publicly give away, or publicly offer invites.",
    'long_22' => "Only invite people you know and trust. Do not offer invites via other trackers, forums, social media, or other public locations. Responding to public invite requests is prohibited. Exception: Staff-designated recruiters may offer invites in approved locations.",
    'short_23' => "Do not request invites or accounts.",
    'long_23' => "You may visit invite forum of ${site_name} when you reach Power User and above. Some trackers only allow offical recruitment, their invites may not be offered or requested on the forums in other trackers. You need to read <a href=\"forums.php?action=viewthread&threadid=15\">Rules on Section of Requesting Invites</a> and <a href=\"forums.php?action=viewthread&threadid=100\">Do Not Request Invites List</a> before you request an invite. You may request invites by messaging users only when they have offered them in the Invites Forum. Unsolicited invite requests, even by private message, are prohibited.",
    'short_24' => "Do not reveal site information in public",
    'long_24' => "Do not mention \"${site_name}\" in any public area. And do not reveal our site url or tracker url. Please hide our Logo when you capturing a screenshot.",
    'short_31' => "Do not engage in ratio manipulation.",
    'long_31' => "Transferring buffer&mdash;or increasing your buffer&mdash;through unintended uses of the BitTorrent protocol or site features (e.g., <a href=\"rules.php?p=requests\">request abuse</a>) constitutes ratio manipulation. When in doubt, send a ${staffpm} asking for more information.",
    'short_32' => "Do not report incorrect data to the tracker (i.e., cheating).",
    'long_32' => "Reporting incorrect data to the tracker constitutes cheating, whether it is accomplished through the use of a modified \"cheat client\" or through manipulation of an approved client.",
    'short_33' => "Do not use unapproved clients.",
    'long_33' => "Your client must be listed on the <a href=\"rules.php?p=clients\">Client Whitelist</a>. You must not use clients that have been modified in any way. Developers interested in testing unstable clients must receive staff approval prior to testing.",
    'short_34' => "Do not modify ${site_name} .torrent files.",
    'long_34' => "Embedding non-${site_name} announce URLs in ${site_name} .torrents is prohibited. Doing so causes false data to be reported and will be interpreted as cheating. This applies to standalone .torrent files and .torrent files that have been loaded into a client.",
    'short_35' => "Do not share .torrent files or your passkey.",
    'long_35' => "Embedded in each ${site_name} .torrent file is an announce URL containing your personal passkey. Passkeys enable users to report stats to the tracker.",
    'short_41' => "Do not blackmail, threaten, or expose fellow users or staff.",
    'long_41' => "Exposing or threatening to expose private information about users for any reason is prohibited. Private information includes, but is not limited to, personally identifying information (e.g., names, records, biographical details, photos). Information that has not been openly volunteered by a user should not be discussed or shared without permission. This includes private information collected via investigations into openly volunteered information (e.g., Google search results).",
    'short_42' => "Do not scam or defraud.",
    'long_42' => "Scams (e.g., phishing) of any kind are prohibited.",
    'short_43' => "Do not disrespect staff decisions.",
    'long_43' => "Disagreements must be discussed privately with the deciding moderator. If the moderator has retired or is unavailable, you may send a ${staffpm}. Do not contact multiple moderators hoping to find one amenable to your cause; however, you may contact a site administrator if you require a second opinion. Options for contacting staff include private message, Staff PM, and #help on ${irc}.",
    'short_44' => "Do not impersonate staff.",
    'long_44' => "Impersonating staff or official service accounts (e.g., Aobing) on-site, off-site, or on IRC is prohibited. Deceptively misrepresenting staff decisions is also prohibited.",
    'short_45' => "Do not backseat moderate.",
    'long_45' => "\"Backseat moderation\" occurs when users police other users. Confronting, provoking, or chastising users suspected of violating rules&mdash;or users suspected of submitting reports&mdash;is prohibited. Submit a report if you see a rule violation.",
    'short_46' => "Do not request special events.",
    'long_46' => "Special events (e.g., freeleech, neutral leech, picks) are launched at the discretion of the staff. They do not adhere to a fixed schedule, and may not be requested by users.",
    'short_47' => "Do not harvest user-identifying information.",
    'long_47' => "It is prohibited to use ${site_name}'s services to harvest user-identifying information of any kind (e.g., IP addresses, personal links) through the use of scripts, exploits, or other techniques.",
    'short_48' => "Do not use ${site_name}'s services (including the tracker, website, and IRC network) for commercial gain.",
    'long_48' => "Commercializing services provided by or code maintained by ${site_name} (e.g., Gazelle, Ocelot) is prohibited. Commercializing content provided by ${site_name} users via the aforementioned services (e.g., user torrent data) is prohibited. Referral schemes, financial solicitations, and money offers are also prohibited.",
    'short_51' => "Do not browse ${site_name} using any free proxy or VPN service.",
    'long_51' => "You may browse the site through a VPN/proxy only if you have paid for this service. This includes (for example) self-hosted VPNs or proxies, services like NordVPN and VPNs or proxies that come with a seedbox. The use of Tor for browsing the site or seeding is not allowed. You may seed from up to 3 IPs at the same time. When in doubt, please send a ${staffpm}. <i class=\"u-colorWarning\">Update! 2021-06-23</i>",
    'short_52' => "Do not abuse automated site access.",
    'long_52' => "All automated site access must be done through the <a href=\"https://github.com/WhatCD/Gazelle/wiki/JSON-API-Documentation\">API</a>. API use is limited to 5 requests within any 10-second window. Scripts and other automated processes must not scrape the site's HTML pages. When in doubt, seek advice from staff.",
    'short_53' => "Do not autosnatch freeleech torrents.",
    'long_53' => "The automatic snatching of freeleech torrents using any method involving little or no user-input (e.g., API-based scripts, log or site scraping, etc.) is prohibited. See ${site_name}'s <a href=\"wiki.php?action=article&id=63\">Freeleech Autosnatching Policy</a> article for more information.",
    'short_61' => "Do not seek or exploit live bugs for any reason.",
    'long_61' => "Seeking or exploiting bugs in the live site (as opposed to a local development environment) is prohibited. If you discover a critical bug or security vulnerability, immediately report it in accordance with ${site_name}'s <a href=\"wiki.php?action=article&id=64\">Responsible Disclosure Policy</a>. Non-critical bugs can be reported in the <a href=\"forums.php?action=viewforum&forumid=16\">Bugs Forum</a>.",
    'short_62' => "Do not publish exploits.",
    'long_62' => "The publication, organization, dissemination, sharing, technical discussion, or technical facilitation of exploits is prohibited at staff discretion. Exploits are defined as unanticipated or unaccepted uses of internal, external, non-profit, or for-profit services. Exploits are subject to reclassification at any time.",
    'short_70' => "Be respectful to all staff members.",
    'long_70' => "Staff on ${site_name} are volunteers who dedicate their time in order to keep the site running, without receiving any compensation. Being disrespectful to them is prohibited, and might result in a warning or worse.",
    'short_71' => "Staff have the final word on rule interpretations.",
    'long_71' => "All rules on ${site_name} may be subject to different interpretations. Since the staff wrote these rules, their interpretation is final. If you need clarification on a rule, or if you think a rule should be restated, please send a ${staffpm}.",

    'ratio_title' => "Ratio & H&R",
    'ratio' => "Ratio",
    'hnr' => "Hit and Run",
    'ratio_used' => "Ratio System Overview:",
    'ratio_summary_a' => "Your <strong>ratio</strong> is calculated by dividing the amount of data you&apos;ve uploaded by the amount of data you&apos;ve downloaded. You can view your ratio in the site header or in the &quot;stats&quot; section of your user profile.",
    'ratio_summary_b' => "To maintain <strong>leeching privileges</strong>, your ratio must remain above a minimum value. This minimum value is your <strong>required ratio</strong>.",
    'ratio_summary_c' => "If your ratio falls below your required ratio, you will be given two weeks to raise your ratio back above your required ratio. During this period, you are on <strong>ratio watch</strong>.",
    'ratio_summary_d' => "If you fail to raise your ratio above your required ratio in the allotted time, your leeching privileges will be revoked. You will be unable to download more data. Your account will remain enabled.",
    'ratio_used_a' => "Required Ratio Overview:",
    'ratio_summary_a_a' => "Your required ratio represents the minimum ratio you must maintain to avoid ratio watch. You can view your required ratio in the site header after the word &quot;required&quot; or in the &quot;stats&quot; section of your user profile.",
    'ratio_summary_b_b' => "Your required ratio is unique; each person&apos;s required ratio is calculated for their account specifically.",
    'ratio_summary_c_c' => "Your required ratio is calculated using (1) the total amount of data you&apos;ve downloaded and (2) the total number of torrents you&apos;re seeding. The seeding total is not limited to snatched torrents (completed downloads)&#8202;&mdash;&#8202;the total includes, but is not limited to, your uploaded torrents.",
    'ratio_summary_d_d' => "The required ratio system lowers your required ratio when you seed a greater number of torrents. The more torrents you seed, the lower your required ratio will be. The lower your required ratio is, the less likely it is that you&apos;ll enter ratio watch.",
    'ratio_table' => "Required Ratio Table",
    'ratio_dl' => "Amount Downloaded",
    'ratio_dl_title' => "These units are actually in base 2, not base 10. For example, there are 1,024 MB in 1 GB.",
    'ratio_re_0' => "Required Ratio (0% seeded)",
    'ratio_re_100' => "Required Ratio (100% seeded)",
    'ratio_sum' => "Required Ratio Calculation:",
    'ratio_1' => "<strong>1: Determine the maximum and minimum possible values of your required ratio.</strong> Using the table above, determine your amount downloaded bracket from the first column.
    Next, locate the values in the adjacent columns. The second column lists the maximum required ratio for each bracket, and the third column lists the minimum required ratio for each
    bracket. The maximum and minimum required ratios are also referred to as the <strong>0% seeded</strong> and <strong>100% seeded</strong> required ratios, respectively.",
    'ratio_2' => "<strong>2: Determine the actual required ratio.</strong> Your actual required ratio will be a number that falls between the maximum and minimum required ratio values determined in the
    previous step. To determine your actual required ratio, the system first uses the maximum required ratio (0% seeded) and multiplies it by the value [1 &minus; (<var>seeding</var> / <var>snatched</var>)]. Formatted
    differently, the calculation performed by the system looks like this:",
    'ratio_show' => "In this formula, <var>snatched</var> is the number of non-deleted unique snatches you have made. If you snatch a torrent twice, it only counts once. If a snatched torrent is deleted from the site, it is not counted at all.</li>
	<li>In this formula, <var>seeding</var> is the average number of torrents you&apos;ve seeded over a 72 hour period within the last week. If you&apos;ve seeded a torrent for less than 72 hours within the last week, it will not raise your seeding total. Please note that while it is possible to seed more torrents than you have snatched, the system effectively caps the value at 100% of your snatched amount.</li>",

    'ratio_3' => "<strong>3: Round, if necessary.</strong> The value determined in the previous step is rounded up to your minimum required ratio (100% seeded) if necessary. This step is required because
    most amount downloaded brackets have a minimum required ratio (100% seeded) greater than zero, and the value returned by the above calculation is zero when seeding equals snatched.",
    'ratio_summary_1' => "Required Ratio Details:",
    'ratio_summary_1_con' => "<li>If you stop seeding for one week, your required ratio will become the maximum required ratio (0% seeded) for your amount downloaded bracket. Once you have resumed seeding for a 72 hour period, your required ratio will decrease according to the above calculations.</li>
	<li>If your download total is less than 5 GB, you won&apos;t be eligible for ratio watch, and you will not need a required ratio. In this circumstance, your required ratio will be zero regardless of your seeding percentage.</li>
	<li>If your download total is less than 20 GB and you are seeding a number of torrents equal to 100% of your snatches, your required ratio will be zero.</li>
	<li>As your download total increases, your minimum (100% seeded) and maximum (0% seeded) required ratios taper together. After you have downloaded 100 GB, those values become equal to each other. This means that users with download totals greater than or equal to 100 GB have a minimum required ratio (100% seeded) of 0.60 from that point forward.</li>",

    'ratio_summary_2' => "Required Ratio Example:",
    'ratio_summary_2_con' => "<li>In this example, Rippy has downloaded 25 GB. Rippy falls into the 20&ndash;30 GB amount downloaded bracket in the table above. Rippy&apos;s maximum required ratio (0% seeded) is 0.30, and his minimum required ratio (100% seeded) is 0.05.</li>
    <li>In this example, Rippy has snatched 90 torrents, and is currently seeding 45 torrents.</li>
    <li>To calculate Rippy&apos;s actual required ratio, we take his maximum required ratio (0% seeded), which is 0.30, and multiply it by [1 &minus; (<var>seeding</var> / <var>snatched</var>)] (which is 0.50). Written out: <samp>0.30 * [1 &minus; (45 / 90)] = 0.15</samp></li>
	<li>The resulting required ratio is 0.15, which falls between the maximum required ratio of 0.30 and the minimum required ratio of 0.05 for his amount downloaded bracket.</li>
	<li>If Rippy&apos;s on-site required ratio was listed as a value greater than the calculated value, this would be because he hadn&apos;t seeded those 45 torrents for a 72 hour period in the last week. In this case, the system would not be counting all 45 torrents as seeded.</li>",

    'ratio_summary_3' => "Ratio Watch Overview:",
    'ratio_summary_3_con' => "<li>Everyone gets to download their first 5 GB before ratio watch eligibility begins.</li>
    <li>If you&apos;ve downloaded more than 5 GB and your ratio does not meet or surpass your required ratio, you will be put on ratio watch and have <strong>two weeks</strong> to raise your ratio above your required ratio.
    </li>
    <li>If you download 10 GB while on ratio watch, your leeching privileges will automatically be disabled.</li>
    <li>If you fail to leave ratio watch within a two week period, you will lose leeching privileges. After losing leeching privileges, you will be unable to download more data. Your account will remain enabled.
    </li>
    <li>The ratio watch system is automated and cannot be interrupted by staff.</li>",

    'ratio_summary_4' => "Leaving Ratio Watch:",
    'ratio_summary_4_con' => "<li>To leave ratio watch, you must either raise your ratio by uploading more, or lower your required ratio by seeding more. Your ratio must be equal to or above your required ratio in order for ratio watch to end.</li>
    <li>If you fail to improve your ratio by the time ratio watch expires and lose leeching privileges, your required ratio will be temporarily set to the maximum possible requirement (as if 0% of snatched torrents were being seeded).</li>
    <li>After losing leeching privileges, in order to adjust the required ratio so that it reflects the actual number of torrents being seeded, you must seed for a combined 72 hours within a weeklong period. After 72 hours of seeding occur, the required ratio will update to reflect your current seeding total, just as it would for a leech-enabled user.</li>
    <li>Leeching privileges will be restored once your ratio has become greater than or equal to your required ratio.</li>",
    'hnr_rules_body' => "<strong>What is Hit and Run (H&R):</strong><br/><ul>
    <li>H&R means after snatched a torrent, the downloader doesn't seed or the seeding time doesn't meet the requirement. A torrent doesn't meet the requirement will make your H&R +1.</li>
    <li>After downloading 20% data of a torrent, you need to seed until meet the requirement. The requirement is totally seed for 48 hours in 2 weeks or at least one full copy.</li>
    <li>Once your H&Rs reach 10, your downloading priviledge will be temporarily disabled. You may eliminate your H&Rs by seeding or \"<a href='bonus.php'>buying safety</a>\".</li>
    <li>H&R static is determined by tracker data. So only noticing your client is not enough. For safety, we recommend you check your <a href='torrents.php?type=downloaded'>H&R record page</a> before stop seeding.</li>
    <li>Please read <a href='wiki.php?action=article&id=68'>H&R FAQ</a> for more explanation.</li>
    </ul>",




    'requests_title' => "Requests",
    'requests_summary' => "<li><li>
    <strong>Do not make requests for torrents that break the rules.</strong> It is your responsibility that the request follows the rules. Otherwise,your request will be deleted, and you will not get your bounty back.</li>
    <li>Do not request multiple torrents in a single request. This means no requesting two movies (ie: I am looking for Casablanca and Fight Club) or requesting two torrents of the same movie (ie: I want a high definition rip and a 700MiB AVI) in one request.You may ask for multiple formats, but you cannot specify all of them.</li>
    <li><strong>Do not unfill requests for trivial reasons.</strong> If you did not specify in your request what you wanted (such as bitrates or a particular edition), do not unfill and later change the description. Do not unfill requests if you are unsure of what you are doing (e.g. the filled torrent may be a transcode, but you don't know how to tell). Ask for help from <a href=\"staff.php\">first-line support or staff</a> in that case. You may unfill the request if the torrent does not fit your specifications stated clearly in the request.</li>
    <li><strong>All users must have an equal chance to fill a request.</strong> Trading upload credit is not allowed. Abusing the request system to exchange favors for other users is not tolerated. That includes making specific requests for certain users (whether explicitly named or not). Making requests for releases, and then unfilling so that one particular user can fill the request is not allowed. If reported, both the requester and user filling the request will receive a warning and lose the request bounty.</li>
    <li><strong>No manipulation of the requester for bounty.</strong> The bounty is a reward for helping other users&#8202;&mdash;&#8202;it should not be a ransom. Any user who refuses to fill a request unless the bounty is increased will face harsh punishment.</li>",


    'collages_title' => "Collages",
    'collages_summary' => "<li>Collages are not used for a list of an actor/actress or director's filmography as the artist pages already exist for this purpose.</li>
    <li>Every collage must have at least 3 torrent groups in it, except for collages of type \"Production Company\", \"Personal\", and \"Staff Picks\".</li>
        <li>Vandalizing of collages will be taken very seriously, resulting in collage editing privileges being removed (at a minimum).</li>
        <li>If something is a published Best Of (for instance, \"Best Movies of the 1990's\") then it should refer to a respected critic, filmmaker, actor, or publication. You may not make a collage for your favorite movies, unless it is a personal collage (available to Power User and above).</li>
        <li>Collages may focus on: genres, production companies, winners/nominees of awards, a series of movies, or any other quantifiable thing that would connect a group of movies together (eg: Horror Film Remakes, Films about the War in the Middle East, etc).</li>
        <li>Please check to see that a similar collage does not already exist. If a similar collage does exist, please contribute to the existing collage.</li>
        <li>Make sure the movies fit with the given theme or meet any requirements in the Collage Information before you add them to a specific collage.</li>
        <li>Please give your collage an appropriate title and a decent description explaining its purpose.</li>
        <li>Collages can be locked by a staff. A locked collage is either definitive or updated regularly. If you find a locked collage missing movies or having other errors, feel free to report it.</li>
        <li>If you are unsure if a collage follows all of the above rules, send a <a href='staff.php'>Staff PM</a> before creating one.</li>",


    'clients_title' => "Clients",
    'clients_list' => "Client Whitelist",
    'clients_summary' => "Client rules are how we maintain the integrity of our swarms. This allows us to filter out disruptive and dishonest clients that may hurt the performance of either the tracker or individual peers.</br></br>
    <strong><a href='https://github.com/c0re100/qBittorrent-Enhanced-Edition/releases'>Modified clients</a> may lead to data statistics errors. Using them will cause you to be warned or even disabled. Please use official clients with three digit version number.</strong>",

    'upload_title' => "Upload",
    'upload_rules' => "Upload Rules",
    'upload_search' => "Filter (empty to reset)",
    'upload_search_note' => "Example: The search term <strong>HD</strong> returns all rules containing <strong>HD</strong>. The search term <strong>HD+trump</strong> returns all rules containing both <strong>HD</strong> and <strong>trump</strong>.",

    'upload_h1k' => "Upload What",
    'upload_h11k' => "Allowed Content",
    'upload_h12k' => "Specifically Banned",
    'upload_h13k' => "Scene Uploads",
    'upload_h13k_a' => "<a href='wiki.php?action=article&amp;id=140'>Scene</a> Uploads",

    'upload_h2k' => "Required Information",
    'upload_h21k' => "Naming",
    'upload_h22k_t' => "Torrent Description",
    'upload_h22k' => "Torrent Description",
    'upload_r220' => "Overview",
    'upload_r220_note' => "This diagram is an overview of duplicates and trumping rules.",
    'upload_h23k_t' => "Movie Posters",
    'upload_h23k' => "Movie posters: You have to provide a poster for your upload (VHS and DVD covers are also usable). Try your best to search, a screenshot including the movie title is accepted only when there is nothing available.",
    'upload_h24k_t' => "Other Release Information",
    'upload_h24k' => "Other release information: Anything you entered on the upload page should represent the actual situation of the resource.",

    'upload_h3k' => "Formats Explanation",
    'upload_h31k' => "Standard Definition (SD)",
    'upload_h32k' => "High Definition (HD)",
    'upload_h33k' => "Ultra High Definition (UHD)",
    'upload_h34k' => "Untouched & Remux",
    'upload_h35k' => "Extra",
    'upload_h36k' => "External Subtitles",

    'upload_h4k' => "Coexisting",
    'upload_h40k' => "Overview",
    'upload_h41k' => "Standard Definition (SD)",
    'upload_h42k' => "High Definition (HD)",
    'upload_h43k' => "Ultra High Definition (UHD)",
    'upload_h44k' => "Untouched & Remux",
    'upload_h45k' => "Extra",
    'upload_h46k' => "Others",

    'upload_h5k' => "Trumping",
    'upload_h51k' => "Sources",
    'upload_h52k' => "Quality",
    'upload_h53k' => "Inactivity",
    'upload_h54k_t' => "Trumpable Marks",
    'upload_h54k' => "Trumpable marks: these marks will be added on torrents that doesn't meet our requirements.",

    'upload_h6k' => "Others",
    'upload_h61k' => "Don't upload things that you don't have full access to. You have to make sure that you can do anything to the torrents or content before you upload them no matter where they are (locally or on the seedbox).",
    'upload_h62k' => "Don't upload torrents that you don't plan to seed. We ask you to seed at least 48 hours in 2 weeks or until the ratio reach 1. This rule is also for uploaders, please read <a href='rules.php?p=ratio'>H&R Rules</a> for more information.",
    'upload_h63k' => "Seeding as long as you can. ${site_name} plan to be a library for all movies and all formats permanently. Longer seeding, better tracker. As a member of ${site_name}, you need to be strict with yourself.",
    'upload_h64k' => "Consider your situation when uploading torrents. You may reduce your upload frequency if your net speed is slow. DO NOT limit your bandwidth deliberately.",

    'upload_introk' => "Introduction",
    'upload_introk_note' => "<p>The uploading rules below are overwhelmingly long and detailed for a reason. The length is necessary to explain the rules clearly and thoroughly. A summary of each rule is in <span style=\"font-weight: bold;\">bold text</span> before the actual rule for easier reading. You may also find the corresponding rule sections in the <a href=\"#Index\">Index</a>. The corresponding <a href=\"#\">&uarr;</a> (move one level up) and <a href=\"#Index\">rule section links</a> (move down into the document) help provide quick navigation.</p>
    <p>Before you upload anything, if you are still unsure of what a rule means, PLEASE ask your questions at any of the following points of site user support: <a href=\"staff.php\">First-Line Support</a>, <a href=\"forums.php?action=viewforum&amp;forumid=16\">the Help Forum</a>, or <a href=\"wiki.php?action=article&amp;name=IRC\"><?=BOT_HELP_CHAN?> on IRC</a>. Send a <a href=\"staffpm.php\">Staff PM</a> addressed to staff if other support has directed you to a moderator or if support has been unhelpful in your particular case. If you find any dead or broken links in the upload rules, send a <a href=\"staffpm.php\">Staff PM</a> addressed to staff, and include in your message the upload rule number (e.g. <a href=\"#r2.4.3\">2.4.3</a>) and preferably the correct link to replace the broken one.</p>",
    'upload_h11k_note' => "<ul><li id='r1.1.1'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.1'>1.1.1.</a>
    <strong>Feature film: </strong>A feature film runs for more than 40 minutes. If there is a movie longer than short films and shorter than feature films, then you need to search on <a href='https://imdb.com/' target='_blank'>IMDb</a>.
   </li>
   <li id='r1.1.2'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.2'>1.1.2.</a>
       <strong>Short film: </strong>A short film can be seconds or 45 minutes long.
   </li>
   <li id='r1.1.3'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.3'>1.1.3.</a>
   <strong>Stand-up comedy: </strong>Stand-up comedy is a comedy performance and narrative craft whereby a comedian communicates to a live audience, speaking directly to them through a microphone. DO NOT upload unofficial performances.
   </li>
   <li id='r1.1.4'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.4'>1.1.4.</a>
       <strong>Miniseries: </strong>a miniseries (or mini-series) is a television show that tells a story in a predetermined, limited number of episodes. It's not TV series, cause it doesn't have consecutive seasons. Please <a href='forums.php?action=viewthread&threadid=21'>ask for staff approval</a> if you are not sure.
   </li>
   <li id='r1.1.5'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.5'>1.1.5.</a> <strong>Anthology series: </strong>An anthology series is a radio, television, or film series that spans through different genres, and presents a different story and a different set of characters in each episode, season, segment or short. Each episode of the anthology series should be uploaded separately. Please <a href='forums.php?action=viewthread&threadid=21'>ask for staff approval</a> before uploading an anthology series.
   </li>
   <li id='r1.1.6'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.6'>1.1.6.</a>
       <strong>Documentary series: </strong>A documentary likes <a href='#r1.1.4'>1.1.4</a>, each season has a theme (e.g. BBC's Blue Planet 1 & 2); or likes <a href='#r1.1.5'>1.1.5</a>, each episode is self-referential (e.g. ESPN 30 for 30) is allowed on ${site_name}. Please <a href='forums.php?action=viewthread&threadid=21'>ask for staff approval</a> if you are not sure.
   </li>
   <li id='r1.1.7'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.7'>1.1.7.</a>
       <strong>Live performance: </strong>Officially released concert, performance art, filmed theatre and so on are allowed. Bootlegs are strictly forbidden.
   </li>
   <li id='r1.1.8'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.8'>1.1.8.</a>
       <strong>Movie collection: </strong>Untouched boxsets or a collection of movies torrents are allowed only when the movies share one or more discs and therefore can't be separated. However, encodes MUST be uploaded separately.
   </li></ul>",
    'upload_h12k_note' => "<ul>
       <li id='r1.2.1'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.1'>1.2.1.</a>
       <strong>Pre-retail: </strong>pre-retail content (CAM, TS, TC, R5, DVDScr, etc.) is not allowed.
   </li>
   <li id='r1.2.2'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.2'>1.2.2.</a>
       <strong>TV shows: </strong>TV shows or series are not allowed.
   </li>
   <li id='r1.2.3'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.3'>1.2.3.</a>
       <strong>Pornography: </strong>a film that is tagged pornography by IMDb is not allowed here.
   </li>
   <li id='r1.2.4'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.4'>1.2.4.</a>
       <strong>MV compilations: </strong>They are not full length content.
   </li>
   <li id='r1.2.5'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.5'>1.2.5.</a>
       <strong>Sports videos: </strong>Only documentaries about sports are allowed. Please <a href='forums.php?action=viewthread&threadid=21'>ask for staff approval</a> before uploading if you are not sure.
   </li>
   <li id='r1.2.6'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.6'>1.2.6.</a>
       <strong>Fan edits: </strong>only official releases of films are allowed.
   </li>
   <li id='r1.2.7'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.7'>1.2.7.</a>
       <strong>Video guide: </strong>Video guides are not allowed. Filmmaking-related content is allowed <a href='forums.php?action=viewthread&threadid=21'>under staff approval</a>.
   </li>
   <li id='r1.2.8'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.8'>1.2.8.</a>
       <strong>Non-video torrents: </strong>your torrent MUST contains a video file. archive formats are not allowed.
   </li>
   <li id='r1.2.9'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.9'>1.2.9.</a>
       <strong>Movie packs: </strong>One movie per torrent. Boxsets contain several movies should be upload in untouched form. Please read rule <a href='#r1.1.4'>1.1.8</a> for more information.
   </li>
   <li id='r1.2.10'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.10'>1.2.10.</a>
       <strong>Movie & extras together: </strong>Except for untouched, extras must be uploaded separately from the main movie.
   </li>
   <li id='r1.2.11'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.11'>1.2.11.</a>
       <strong>Transcodes: </strong>transcodes are not allowed. The source for all encodes should be the original untouched forms.
   </li>
   <li id='r1.2.12'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.12'>1.2.12.</a>
       <strong>Low-quality releases: </strong>Current list： aXXo, BRrip, CM8, CrEwSaDe, DNL, EVO (except WEB-DLs), FaNGDiNG0, FRDS (except REMUXs), HD2DVD, HDTime, iPlanet, KiNGDOM, Leffe, mHD, mSD, nHD, nikt0, nSD, NhaNc3, PRODJi, RDN, SANTi,  STUTTERSHIT, TERMiNAL (low bitrate UHD), ViSION, WAF, x0r, YIFY, PSP/iPad/mobile device preset encoded。
   </li>
   <li id='r1.2.13'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.13'>1.2.13.</a>
       <strong>Trailer compilations: </strong>Trailer compilations are not allowed.
   </li>
   <li id='r1.2.14'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.14'>1.2.14.</a>
       <strong>Specifically banned: </strong>Anything listed on our <s><a href='torrents.php?action=do_not_upload_movie_list'>blacklist</a></s> (building) is not allowed. <strong>Advertising of any kind is prohibited</strong> (including but not limited to promotional links written in the title of the video or audio track, large promotional images included within the release description, etc.). <i class=\"u-colorWarning\">Update! 2021-08-06</i>
   </li><ul>",

    'upload_h21k_note' => "<ul>

   <li id='r2.1.1'><a href='#h2.1'><strong></strong></a> <a href='#r2.1.1'>2.1.1.</a> <strong>File/Folder name should use the original title or official English title of the movie. (e.g. the English title on the poster, which is prior to IMDb.)</strong>
       <ul>
       <li id='r2.1.1.1'><a href='#r2.1.1'><strong></strong></a> <a href='#r2.1.1.1'>2.1.1.1.</a> Internal Remuxes need to include original release year, resolution, video & audio codec (no order limitation). (e.g. The.Thing.1982.1080p.AVC.DTS-HD.MA, or Citizen Kane (1941) 1080p H264 FLAC)
       </li>
       <li id='r2.1.1.2'><a href='#r2.1.1'><strong></strong></a> <a href='#r2.1.1.2'>2.1.1.2.</a> Torrents whose file/folder name contains irrelevant and meaningless content will be marked as \"trumpable\". (e.g. \"2. The Incredible Hulk\")
       </li>
       <li id='r2.1.1.3'><a href='#r2.1.1'><strong></strong></a> <a href='#r2.1.1.3'>2.1.1.3.</a> CN Quality Slot (defined by rule <a href='#r4.0.1'>4.0.1</a>) torrents will be marked as \"trumpable\" if there are non-Chinese or non-English characters in the file/folder names.</li>
       </ul>
   </li>
   <li id='r2.1.2'><a href='#h2.1'><strong></strong></a> <a href='#r2.1.2'>2.1.2.</a> <strong>Renaming group releases (by P2P groups or Scene) is not allowed unless they disagree with rule <a href='#r2.1.1'>2.1.1</a> or our file/folder name requirements.</strong>
   </li>
   <li id='r2.1.3'><a href='#h2.1'><strong></strong></a> <a href='#r2.1.3'>2.1.3.</a> <strong>Keep your torrent as simple as possible. </strong>Don't include: samples, screenshots, desktop.ini/thumbs.db files, or anything that not relevant to your upload, otherwise your upload will be marked \"trumpable\". Ripping logs etc. are allowed in DVD/BD structures. Please <a href='subtitles.php'>upload subtitle files separately</a> rather than include them in the torrents. <i class=\"u-colorWarning\">Update! 2021-06-23</i>
   </li>
   <li id='r2.1.4'><a href='#h2.1'><strong></strong></a> <a href='#r2.1.4'>2.1.4.</a> <strong>DVD/BD structures should not be modified, only the top-level folder can be renamed.</strong>
   </li>
</ul>",
    'upload_h22k_note' => "<ul>
   <li id='r2.2.1'><a href='#h2.2'><strong></strong></a> <a href='#r2.2.1'>2.2.1.</a> <strong>Screenshots: You should provide at least 3 display resolution PNG screenshots in \"Torrent Description\" area at upload page. They have to be saved at <a href='upload.php?action=image'>official image host</a>. You can also use </strong> <a href='https://pixhost.to'>pixhost.to</a>, <a href='https://ptpimg.me'>ptpimg.me</a>, <a href='https://yes.ilikeshots.club/'>yes.ilikeshots.club</a>, <a href='https://imgbox.com'>imgbox.com</a> or <a href='https://img.pterclub.com'>img.pterclub.com</a> as an alternative. You need to provide at least 1 screenshot per episode for series-kind torrents.
   </li>
   <li id='r2.2.2'><a href='#h2.2'><strong></strong></a> <a href='#r2.2.2'>2.2.2.</a> <strong>Mediainfo: You must use MediaInfo or BDInfo for Blu-ray discs to provide the specifications of your uploads. You have to provide information for each if there are multiple video files in your torrents. Editing MediaInfo log is strictly forbidden. </strong>Please report it if you sure it is not right.
   </li>
   <li id='r2.2.3'><a href='#h2.2'><strong></strong></a> <a href='#r2.2.3'>2.2.3.</a> <strong>No advertising. </strong>P2P and scene groups' names in file/folder names are not counted as advertisements.
   </li>
</ul>",

    'upload_h23k_note' => "
<ul>
   <li id='r2.3.1'><a href='#h2.3'><strong></strong></a> <a href='#r2.3.1'>2.3.1.</a> <strong>Fan art is not allowed if there exists an official art.</strong>
   </li>
   <li id='r2.3.2'><a href='#h2.3'><strong></strong></a> <a href='#r2.3.2'>2.3.2.</a> <strong>A theatrical poster is the best choice. The photo of physical disc is not allowed.</strong>
   </li>
   <li id='r2.3.3'><a href='#h2.3'><strong></strong></a> <a href='#r2.3.3'>2.3.3.</a> <strong>You need to follow rule <a href='#r2.2.1'>2.2.1</a> to save posters if you didn't fetch them by Auto-fill.</strong>
   </li>
</ul>",
    'upload_h24k_note' => "
<ul>
   <li id='r2.4.1'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.1'>2.4.1.</a> <strong>You must fill an IMDb link for your upload if it exists. </strong>If you didn't fetch a synopsis by Auto-fill, then write one by yourself.
   <ul>
           <li id='r2.4.1.1'><a href='#r2.4.1'><strong></strong></a> <a href='#r2.4.1.1'>2.4.1.1.</a> <strong>You have to provide full tracklists and retail links (e.g. Amazon) in \"Torrent Description\" when uploading concerts. IMDb links are also required if they exist.</strong>
           </li>
       </ul>
   </li>
   <li id='r2.4.2'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.2'>2.4.2.</a> <strong>Please check \"Edition Information\" and select suitable marks if your upload is a special edition from the original theatrical one (e.g. Director's Cut, Unrated, Dubs, etc.).</strong> Any applicable feature mark (e.g. HDR10, Dolby Vision, Dolby Atmos, 3D, 2in1, etc.) should also be selected. Please read <a href='wiki.php?action=article&id=2'>this article</a> for more information.
   </li>
   <li id='r2.4.3'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.3'>2.4.3.</a> <strong>Please check \"Self-rip\" if you are uploading your own encode or rip.</strong>
   </li>
   <li id='r2.4.4'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.4'>2.4.4.</a> <strong>You must select subtitle options.</strong>
   </li>
   <li id='r2.4.5'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.5'>2.4.5.</a> <strong>You must add relevant information (e.g. source, notes, x264 logs, etc.) to \"Torrent Description\" if it exists.</strong> We encourage you to provide this kind of information if you rip the video by yourself.
   </li>
   <li id='r2.4.6'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.6'>2.4.6.</a> <strong>Tags added for movies should be objective. </strong>IMDb tags are authoritative. However, unreliable (e.g. subjective, political, etc.) tags will be deleted. Tags represent genre (e.g. drama, sci.fi, etc.), not specific things or redundant information which can be provided by other funtions (e.g. steven.spielberg, korean, imdb.top.250, etc.).
   </li>
   <li id='r2.4.7'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.7'>2.4.7.</a> <strong>Please add a trailer if possible. The better you edit, people will more likely to download your torrents. </strong>Avoid to uglify the movie.
   </li>
   <li id='r2.4.8'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.8'>2.4.8.</a> <strong>Encode information, logs, comparison pictures are important evidence for encoders' great work. You are encouraged to contain as complete information as possible when re-uploading rather than only providing 3 screenshots.</strong>
</ul>",

    'upload_h31k_note' => "
<ul>
   <li id='r3.1.1'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.1'>3.1.1.</a> <strong>SD torrents are any torrents that don't meet requirements of HD torrents. (see <a href='#r3.2.1'>3.2.1</a>).</strong>
   <ul>
           <li id='r3.1.1.1'><a href='#r3.1.1'><strong></strong></a> <a href='#r3.1.1.1'>3.1.1.1.</a> <strong>Any x264 encodes from SD sources should not be resized and need to be tagged according to the storage resolution.</strong>
           </li>
           <li id='r3.1.1.2'><a href='#r3.1.1'><strong></strong></a> <a href='#r3.1.1.2'>3.1.1.2.</a> <strong>x264 encodes from HD and UHD sources must use the 480p (maximum resolution of 854x480 pixels) or 576p (maximum resolution of 1024x576 pixels) resolutions.</strong>
           </li>
       </ul>
   </li>
   <li id='r3.1.2'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.2'>3.1.2.</a> <strong>SD encodes must use x264 codec and MKV container.</strong>
   </li>
   <li id='r3.1.3'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.3'>3.1.3.</a> <strong>Wrong codecs, containers, and resolutions can be tolerated when untranscoded preferred formats are not available. </strong>Unless there are huge quality improvements, or wrong formats will not be allowed if there are proper ones. See relevant <a href='#h4.1'>Coexisting</a>/<a href='#h5.2'>Trumping</a> rules or ask <a href='forums.php?action=viewthread&threadid=22'>here</a>.
   </li>
   <li id='r3.1.4'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.4'>3.1.4.</a> <strong>Use encodes as source to rip is strictly forbidden.</strong>
   </li>
   <li id='r3.1.5'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.5'>3.1.5.</a> <strong>In one movie, once a Blu-ray 720p encode (better quality) uploaded, all SD encodes will be deleted.</strong> Uploading SD encodes is not allowed if a Blu-ray 720p encode has existed. Exception: DVD encodes can coexist with Blu-ray 720p encodes if there is significant different between DVD and Blu-ray discs, or If you think the SD encode that you want to upload is of <strong>special value</strong>, please see <a href='forums.php?action=viewthread&threadid=21'>Can I upload this</a>. <i class=\"u-colorWarning\">Update! 2021-08-06</i>
   </li>
   <li id='r3.1.6'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.6'>3.1.6.</a> See <a href='#h4.1'>relevant rules section</a> for more information on SD coexistence.
   </li>
</ul>",
    'upload_h32k_note' => "
<ul>
   <li id='r3.2.1'><a href='#h3.2'><strong></strong></a> <a href='#r3.2.1'>3.2.1.</a> <strong>720p (maximum resolution of 1280x720 pixels) and 1080p (maximum resolution of 1920x1080 pixels) are allowed resolutions.</strong>
   </li>
   <li id='r3.2.2'><a href='#h3.2'><strong></strong></a> <a href='#r3.2.2'>3.2.2.</a> <strong>HD encodes must use x264 codec and MKV container. </strong>(HDR x265 1080p encodes are allowed, see <a href='#r4.2.2'>4.2.2</a>.)
   </li>
   <li id='r3.2.3'><a href='#h3.2'><strong></strong></a> <a href='#r3.2.3'>3.2.3.</a> <strong>Wrong codecs, containers, and resolutions can be tolerated when untranscoded preferred formats are not available. </strong>Unless there are huge quality improvements, or wrong formats will not be allowed if there are proper ones. See relevant <a href='#h4.1'>Coexisting</a>/<a href='#h5.2'>Trumping</a> rules or ask <a href='forums.php?action=viewthread&threadid=22'>here</a>.
   </li>
   <li id='r3.2.4'><a href='#h3.2'><strong></strong></a> <a href='#r3.2.4'>3.2.4.</a> <strong>The source of HD encodes must be Blu-ray, HD-DVD, HDTV or WEB. </strong>Any other source must be <a href='forums.php?action=viewthread&threadid=21'>approved by staff</a>.
   </li>
   <li id='r3.2.5'><a href='#h3.2'><strong></strong></a> <a href='#r3.2.5'>3.2.5.</a> See <a href='#h4.2'>relevant rules section</a> for more information on HD coexistence.
   </li>
</ul>",

    'upload_h33k_note' => "
<ul>
   <li id='r3.3.1'><a href='#h3.3'><strong></strong></a> <a href='#r3.3.1'>3.3.1.</a> <strong>2160p (maximum resolution of 4096x2160 pixels) is allowed resolution.</strong>
   </li>
   <li id='r3.3.2'><a href='#h3.3'><strong></strong></a> <a href='#r3.3.2'>3.3.2.</a> <strong>UHD sources featuring HDR (High Dynamic Range) must be encoded as such.</strong>
   </li>
   <li id='r3.3.3'><a href='#h3.3'><strong></strong></a> <a href='#r3.3.3'>3.3.3.</a> <strong>UHD encodes must use x265 codec and MKV container. Web source UHD torrents with x264 codec are allowed.</strong> <i class=\"u-colorWarning\">Update! 2021-08-06</i>
   <ul>
           <li id='r3.3.3.1'><a href='#r3.3.3'><strong></strong></a> <a href='#r3.3.3.1'>3.3.3.1.</a> <strong>SDR UHD encodes may use x264 codec if allowed by <a href='#r4.3.1.2'>4.3.1.2</a>.</strong>
           </li>
       </ul>
   </li>
   <li id='r3.3.4'><a href='#h3.3'><strong></strong></a> <a href='#r3.3.4'>3.3.4.</a> <strong>Wrong codecs, containers, and resolutions can be tolerated when untranscoded preferred formats are not available. </strong>Unless there are huge quality improvements, or wrong formats will not be allowed if there are proper ones. See relevant <a href='#h4.1'>Coexisting</a>/<a href='#h5.2'>Trumping</a> rules or ask <a href='forums.php?action=viewthread&threadid=22'>here</a>.
   </li>
   <li id='r3.3.5'><a href='#h3.3'><strong></strong></a> <a href='#r3.3.5'>3.3.5.</a> See <a href='#h4.2'>relevant rules section</a> for more information on HD coexistence.
   </li>
</ul>",

    'upload_h34k_note' => "
<ul>
   <li id='r3.4.1'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.1'>3.4.1.</a> <strong>Untouched uploads are identical copies of retail discs. </strong>They may include menus, extras and additional audio tracks (full VOB_IFO/M2TS rips). HD and UHD remuxes would be stripped down to the main movie only. Only warnings may be stripped from full untouched uploads.
   <ul>
       <li id='r3.4.1.1'><a href='#r3.4.1'><strong></strong></a> <a href='#r3.4.1.1'>3.4.1.1.</a> <strong>Copy protection and region locks must be removed.</strong>
           </li>
       </ul>
   </li>
   <li id='r3.4.2'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.2'>3.4.2.</a> <strong>Untouched DVD rips can use the VOB_IFO (VIDEO_TS folder and contents) or the ISO container.</strong>
   <ul>
       <li id='r3.4.2.1'><a href='#r3.4.2'><strong></strong></a> <a href='#r3.4.2.1'>3.4.2.1.</a> The maximum disc size of DVD5 is 4.37 GiB. The maximum disc size of DVD9 is 7.95 GiB.
           </li>
       </ul>
   </li>
   <li id='r3.4.3'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.3'>3.4.3.</a> <strong>Untouched HDTV captures must use the TS or the MKV container.</strong>
   </li>
   <li id='r3.4.4'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.4'>3.4.4.</a> <strong>Untouched and DIY Blu-ray rips should use the M2TS container or the ISO container.</strong>
   <ul>
       <li id='r3.4.4.1'><a href='#r3.4.4'><strong></strong></a> <a href='#r3.4.4.1'>3.4.4.1.</a> The maximum disc size of BD25 is 23.28 GiB. The maximum disc size of BD50 is 46.57 GiB. The maximum disc size of BD66 is 61.47 GiB. The maximum disc size of BD100 is 93.13 GiB.
       </li>
   </ul>
   </li>
   <li id='r3.4.5'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.5'>3.4.5.</a> <strong>Blu-ray remuxes must use the MKV container. </strong>A remux is a combination of untouched (or losslessly compressed) audio and video, simply muxed together.
   <ul>
       <li id='r3.4.5.1'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.1'>3.4.5.1.</a> <strong>Remuxes must always use the highest quality tracks available on the source disc.</strong>
       </li>
       <li id='r3.4.5.2'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.2'>3.4.5.2.</a> <strong>The remux must be muxed in the following order: Video - Main Audio (marked as default) - Secondary Audio - Subtitles</strong>
       </li>
       <li id='r3.4.5.3'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.3'>3.4.5.3.</a> <strong>PCM Audio and DTS-HD MA for 2.0 Audio and below must be converted to FLAC, do not covert 24 bit DTS-HD MA to 16 bit. PCM Audio for 2.1 and above must be converted to DTS-HD MA or to FLAC.
       </strong>
       </li>
       <li id='r3.4.5.4'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.4'>3.4.5.4.</a> <strong>SRT Subtitles are allowed in a remux.</strong>
       </li>
       <li id='r3.4.5.5'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.5'>3.4.5.5.</a> <strong>Dolby Vision remuxes can use the MP4 container. </strong>See <a href='#r5.2.1.2'>5.2.1.2</a> .
       </li>
       <li id='r3.4.5.6'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.6'>3.4.5.6.</a> <strong>If you checked \"Self-Rip\" for your remux uploads, you must provide eac3to log.</strong> If you re-post the remux from other places, we advice you to paste the log if possible.
        </li>
   </ul>
   </li>
   <li id='r3.4.6'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.6'>3.4.6.</a> <strong>Untouched rips containing only extra content must be uploaded with the main disc as a single torrent.</strong>
   </li>
   <li id='r3.4.7'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.7'>3.4.7.</a> See <a href='#h4.4'>relevant rules section</a> for more information on untouched and remux coexistence.
   </li>
</ul>",

    'upload_h35k_note' => "
<ul>
   <li id='r3.5.1'><a href='#h3.5'><strong></strong></a> <a href='#r3.5.1'>3.5.1.</a> <strong>Extras are video content included on official releases of the movie but not any kind of version of the main movie (e.g. behind the scenes, interviews, etc.).</strong>
   <ul>
           <li id='r3.5.1.1'><a href='#r3.5.1'><strong></strong></a> <a href='#r3.5.1.1'>3.5.1.1.</a> <strong>Extra uploads must check \"Not Main Movie\" option on upload page.</strong>
           </li>
       </ul>
   </li>
   <li id='r3.5.2'><a href='#h3.5'><strong></strong></a> <a href='#r3.5.2'>3.5.2.</a> <strong>Extras are allowed only in complete packs of official retail releases.</strong>
   <ul>
           <li id='r3.5.2.1'><a href='#r3.5.2'><strong></strong></a> <a href='#r3.5.2.1'>3.5.2.1.</a> <strong>Extra content packs must be identified by distributor/edition, see <a href='#r2.5.2'>2.5.2</a> for more information.</strong>
           </li>
       </ul>
   </li>
   <li id='r3.5.3'><a href='#h3.5'><strong></strong></a> <a href='#r3.5.3'>3.5.3.</a> <strong>Discs containing only extra content must be uploaded with the main disc as a single torrent.</strong>
   </li>
   <li id='r3.5.4'><a href='#h3.5'><strong></strong></a> <a href='#r3.5.4'>3.5.4.</a> <strong>An extra with an IMDb page must be uploaded separately.</strong>
   </li>
   <li id='r3.5.5'><a href='#h3.5'><strong></strong></a> <a href='#r3.5.5'>3.5.5.</a> See <a href='#h4.5'>relevant rules section</a> for more information on extras coexistence.
   </li>
</ul>",
    'upload_h36k_note' => "
<ul>
   <li id='r3.6.1'><a href='#h3.6'><strong></strong></a> <a href='#r3.6.1'>3.6.1.</a> <strong>The subtitle uploaded by a user must be synchronized with the specific video file, otherwise it will be deleted directly.</strong>
   </li>
   <li id='r3.6.2'><a href='#h3.6'><strong></strong></a> <a href='#r3.6.2'>3.6.2.</a> <strong>Subtitle formats allowed by ${site_name} are .sub, .idx, .sup, .srt, .vtt, .ass, .smi, and .ssa. </strong>They can also be compressed to .rar, .zip, .7z, .tar, .tgz, or .tar.gz.
   </li>
   <li id='r3.6.3'><a href='#h3.6'><strong></strong></a> <a href='#r3.6.3'>3.6.3.</a> <strong>It's recommend for a subtitle to use the consistent file name to the video file.</strong>You can also add a language tag on the tail, e.g. \"Monsters.Inc.2001.1080p.BluRay.DTS.x264.D-Z0N3.chs.srt\".
   </li>
   <li id='r3.6.4'><a href='#h3.6'><strong></strong></a> <a href='#r3.6.4'>3.6.4.</a> <strong>Unicode subtitles are preferred.</strong>
   </li>
   <li id='r3.6.5'><a href='#h3.6'><strong></strong></a> <a href='#r3.6.5'>3.6.5.</a> <strong>Please upload a single subtitle file for one-file movie. DO NOT compress different languages subtitle files together. </strong>For example, including Simplified Chinese SRT, Traditional Chinese SRT and Chinese & English SRT in a single archive file is not allowed. They should be uploaded separately. <strong>For miniseries, compressing subtitles for episodes together is allowed. </strong><i style=\"color: #ed5454\">Update! 2021-08-06</i>
   </li>
</ul>",


    'upload_h40k_note' => "<ul>
<li id='r4.0.1'><a href='#h4.0'><strong></strong></a> <a href='#r4.0.1'>4.0.1.</a> <strong>Here is a diagram for our slots (special editions are not considered) below. A slot means an empty \"seat\" for a new upload, once was all occupied, the latest upload must trump older torrents or be removed.</strong> <i class=\"u-colorWarning\">Update! 2021-08-06</i>
<div class='TableContainer'>
<table class='Table TableRuleSlot'>
<tr class='Table-rowHeader'>
   <th class='Table-cell is-content'>Content</th>
   <th class='Table-cell is-slot'>Slot type</th>
   <th class='Table-cell is-subtitle'>Subtitle requirement</th>
   <th class='Table-cell is-codec'>Codec</th>
   <th class='Table-cell is-resolution'>Divide by resolution</th>
   <th class='Table-cell is-description'>Description</th>
</tr>

<tr class='Table-row'>
   <td class='Table-cell' rowspan='11'>Main movie</td>
   <td class='Table-cell' rowspan='2'>Encode<br/>CN Quality Slot</td>
   <td class='Table-cell' rowspan='2'>Must softcode a Chinese subtitle<br/>(hardcoded or external subtitles are not counted)</td>
   <td class='Table-cell'>x264</td>
   <td class='Table-cell'>SD / 720p / 1080p (3 slots)</td>
   <td class='Table-cell' rowspan='4'>Trump priority: high quality > small size</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell'>x265</td>
   <td class='Table-cell'>HDR 1080p / 2160p (2 slots)</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell' rowspan='2'>Encode<br/>EN Quality Slot</td>
   <td class='Table-cell' rowspan='2'>Recommended to softcode an English subtitle<br/>(hardcoded subtitles are not counted)</td>
   <td class='Table-cell'>x264</td>
   <td class='Table-cell'>SD / 720p / 1080p (3 slots)</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell'>x265</td>
   <td class='Table-cell'>HDR 1080p / 2160p (2 slots)</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell' rowspan='2'>Encode<br/>Rentention Slot</td>
   <td class='Table-cell' rowspan='2'>No requirement</td>
   <td class='Table-cell'>x264</td>
   <td class='Table-cell'>720p / 1080p (2 slots)</td>
   <td class='Table-cell' rowspan='2'>Trump priority: Higher quality is better when the sizes are the same, smaller size is better when the qualities are the same</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell'>x265</td>
   <td class='Table-cell'>2160p (1 slot)</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell' rowspan='2'>Encode<br/>Feature Slot</td>
   <td class='Table-cell' rowspan='2'>Must softcode a Chinese subtitle or have a Mandarin dub<br/>(hardcoded or external subtitles are not counted)</td>
   <td class='Table-cell'>x264</td>
   <td class='Table-cell'>720p / 1080p (2 slots)</td>
   <td class='Table-cell' rowspan='2'>Trump priority: more Mandarin dubs / special effects subtitles > better Mandarin dubs / special effects subtitles > high quality encode<br/>Only for Non-Chinese movies, only have Mandarin dubs or special effects subtitles is not enough</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell'>x265</td>
   <td class='Table-cell'>2160p (1 slot)</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell'>Remux Slot</td>
   <td class='Table-cell'>No requirement</td>
   <td class='Table-cell'>-</td>
   <td class='Table-cell'>720p / 1080p / 2160p (3 slots)</td>
   <td class='Table-cell'>Trump priority: high quality discs > internal Chinese subtitle</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell'>Untouched Slot</td>
   <td class='Table-cell'>No requirement</td>
   <td class='Table-cell'>-</td>
   <td class='Table-cell'>SD x2 / 720p / 1080p / 2160p<br/>(5 slots)</td>
   <td class='Table-cell'>Trump priority: high quality discs<br/>You may upload ISO images or M2TS containers (BluRay Only)</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell'>DIY Slot</td>
   <td class='Table-cell'>No requirement</td>
   <td class='Table-cell'>-</td>
   <td class='Table-cell'>1080p / 2160p (2 slots)</td>
   <td class='Table-cell'>Trump priority: more Mandarin dubs / special effects subtitles > better Mandarin dubs / special effects subtitles > high quality encode<br/>Only for Non-Chinese movies, only have Mandarin dubs or special effects subtitles is not enough. You may upload ISO images or M2TS containers</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell' rowspan='2'>Extra</td>
   <td class='Table-cell' rowspan='2'>Encode<br/>Quality Slot</td>
   <td class='Table-cell' rowspan='2'>No requirement</td>
   <td class='Table-cell'>x264</td>
   <td class='Table-cell'>SD / 720p / 1080p (3 slots)</td>
   <td class='Table-cell' rowspan='2'>Trump priority: high quality > small size</td>
</tr>
<tr class='Table-row'>
   <td class='Table-cell'>x265</td>
   <td class='Table-cell'>2160p (1 slot)</td>
</tr>
</table>
</div>
<li id='r4.0.2'><a href='#h4.0'><strong></strong></a> <a href='#r4.0.2'>4.0.2.</a> <strong>Slot types</strong> are divided by processing, subtitles, and audio tracks. They tell us the type of resources (be convenient for calling and remembering).
    <ul>
        <li id='r4.0.2.1'><a href='#r4.0.2'><strong></strong></a> <a href='#r4.0.2.1'>4.0.2.1.</a> <strong>CN Quality Slot</strong> torrents <strong>MUST</strong>have softcoded Chinese subtitles (simplified and traditional are both allowed). Chinese, Chinese + English, Chinese + Original (original language subtitle, e.g. Japanese) subtitle combinations only enter CN Quality Slots. All other combinations enter EN Quality Slots.
        </li>
        <li id='r4.0.2.2'><a href='#r4.0.2'><strong></strong></a> <a href='#r4.0.2.2'>4.0.2.2.</a> For non-Chinese movies in <strong>CN Quality Slots</strong>, non-Chinese dubs would be considered as redundant. Non-Chinese movies with a Chinese dub will enter <strong>Feature Slot</strong>.
        </li>
        <li id='r4.0.2.3'><a href='#r4.0.2'><strong></strong></a> <a href='#r4.0.2.3'>4.0.2.3.</a> <strong>EN Quality Slot</strong> torrents <strong>MUST</strong> have an English subtitle (internal or external)If they are non-English films and recommended to set the English subtitle as default. In general, the first torrent without any subtitles will enter this slot, and If the torrent is non-English film it will be marked as \"Trumpable\", see <a href='#r5.4.14'>5.4.14</a>  for more. <i class=\"u-colorWarning\">Update! 2021-08-06</i>
        </li>
    </ul>
</li>
<li id='r4.0.3'><a href='#h4.0'><strong></strong></a> <a href='#r4.0.3'>4.0.3.</a> <strong>Subtitle requirement:</strong> If multi-language subtitles contained Chinese (e.g. chi&eng etc.), they will be marked and treated as Chinese subtitles.
</li>
<li id='r4.0.4'><a href='#h4.0'><strong></strong></a> <a href='#r4.0.4'>4.0.4.</a> <strong>Description: </strong>The basis of duplicates and trumping for the slot and the priority of trumping elements.
<ul>
   <li id='r4.0.4.1'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.1'>4.0.4.1.</a> <strong>Quality Slot: </strong>This slot only considers encode quality. Encodes should keep the video looks the same as untouched disc and reduce the bitrate as much as possible. Audio tracks should obey the requirements of <a href='#r5.4.3'>5.4.3</a>, redundant dub tracks are not allowed.
   </li>
   <li id='r4.0.4.2'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.2'>4.0.4.2.</a> <strong>Rentention Slot: </strong>This slot considers size and encode quality. Encodes should keep the video looks good and reduce the bitrate as much as possible. This slot basically refers to 0day/Scene encodes, redundant dub tracks are not allowed.
   </li>
   <li id='r4.0.4.3'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.3'>4.0.4.3.</a> <strong>Feature Slot: only for non-Chinese movies. </strong>This slot considers mainly the richness of content. Encodes should keep the video looks good and add as many Mandarin dubs & special effects subtitles as possible. Uploaders have to describe what was added on \"Torrent Description\", such as \"Added xx Mandarin dub and a xx special effects subtitle\", and add screenshots to prove special effects subtitle content.
       <ul>
           <li id='r4.0.4.3.1'><a href='#r4.0.4.3'><strong></strong></a> <a href='#r4.0.4.3.1'>4.0.4.3.1.</a> <strong>Mandarin dub: </strong>Only non-Chinese uploads (Cantonese movies are Chinese movies) can add this mark. Dialect dubs (including Cantonese dubs) are not considered as Mandarin dubs.
           </li>
           <li id='r4.0.3.3.2'><a href='#r4.0.3.3'><strong></strong></a> <a href='#r4.0.3.3.2'>4.0.3.3.2.</a> <strong>Special effects subtitle: </strong>Subtitles with special effects such as reflection, flicker, movement, tumble, drift, color, 2D, 3D, split, combination, etc.. The reason why using special effects is to match movie screens as perfect as possible. Only changing the color or font family is not considered as special effects. Please ask on <a href='forums.php?action=viewforum&forumid=31'>Help Forum</a> if you are not sure. You must provide at least two extra screenshot for special effects subtitle (no resolution and format requirements, yet <strong>you must capture effects for plot-related parts of the film</strong> (don't just capture effects about credits or names of filming company or something pointless) adding separately and not counting to <a href='#h2.2'>the basic requirement of 3 screenshots</a>, which means at least 5 in total. <i class=\"u-colorWarning\">Update! 2021-08-06</i>
           </li>
       </ul>
   </li>
   <li id='r4.0.4.4'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.4'>4.0.4.4.</a> <strong>Remux Slot: </strong>This slot considers mainly the quality of source video and audio tracks.
   </li>
   <li id='r4.0.4.5'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.5'>4.0.4.5.</a> <strong>Untouched Slot: </strong>This slot considers mainly the quality of source video and audio tracks.
   </li>
   <li id='r4.0.4.6'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.6'>4.0.4.6.</a> <strong>DIY Slot: only for non-Chinese movies. </strong>This slot considers mainly the quality of source video and audio tracks. If the qualities are the same, more Mandarin dubs & special effects subtitles is preferred. Uploaders have to describe what was added on \"Torrent Description\", such as \"Added xx Mandarin dub and a xx special effects subtitle\", and add screenshots to prove special effects subtitle content.Please note that <strong>Chinese films (including Cantonese, Minnan and other dialect films) are not eligible for this rule and slot</strong>, and uploading of DIY discs of Chinese films is not allowed.If you think the DIY disc of Chinese films that you want to upload is of <strong>special value</strong>, please see <a href='forums.php?action=viewthread&threadid=21'>Can I upload this</a>. <i class=\"u-colorWarning\">Update! 2021-08-06</i>
   </li>
</ul>
</li>
<li id='r4.0.5'><a href='#h4.0'><strong></strong></a> <a href='#r4.0.5'>4.0.5.</a> <strong>For all slots: </strong>Dialect (including Cantonese) movies can not use \"Chinese dubbed\" \"Special effects subtitles\" marks. Mandarin dubs in them are not considered as redundant.
</li>
</ul>",
    'upload_h41k_note' => "
<ul>
   <li id='r4.1.1'><a href='#h4.1'><strong></strong></a> <a href='#r4.1.1'>4.1.1.</a> <strong>There is a EN Quality slot and a CN Quality slot, 2 slots in total for a given movie. </strong><i style=\"color: #ed5454\">New! 2021-08-06</i>
   </li>
   <li id='r4.1.2'><a href='#h4.1'><strong></strong></a> <a href='#r4.1.2'>4.1.2.</a> See <a href='#h3.1'>relevant rules section</a> for more information on SD uploads.
   </li>
</ul>",

    'upload_h42k_note' => "
<ul>
   <li id='r4.2.1'><a href='#h4.2'><strong></strong></a> <a href='#r4.2.1'>4.2.1.</a> <strong>There are 4 720p and 4 1080p x264 encode slots for a given movie.</strong>
   <ul>
           <li id='r4.2.1.1'><a href='#r4.2.1'><strong></strong></a> <a href='#r4.2.1.1'>4.2.1.1.</a> <strong>Each group contains a CN Quality Slot, an EN Quality Slot, a Retention Slot and a Feature Slot. </strong>The Retention Slot should be more compressed, while the Quality Slot should be provided for the highest quality encode available. The Retention Slot should be at least 20% smaller than the Quality Slot to coexist.
           </li>
       </ul>
   </li>
   <li id='r4.2.2'><a href='#h4.2'><strong></strong></a> <a href='#r4.2.2'>4.2.2.</a> <strong>There are 2 extra slots for HDR x265 1080p encodes. </strong>They are independent from the slots defined by <a href='#r4.2.1'>4.2.1</a> and does not interfere with them. They should be provided for the highest quality encode available. <strong>SDR x265 1080p encodes are not allowed.</strong> Exception: You may upload SDR 10-bit x265 1080p encodes for Animations. <strong>But SDR 10-bit x264 encodes are not allowed,</strong> because of their poor compatibility.
   </li>
   <li id='r4.2.3'><a href='#h4.2'><strong></strong></a> <a href='#r4.2.3'>4.2.3.</a> See <a href='#h3.2'>relevant rules section</a> for more information on HD uploads.
   </li>
</ul>",

    'upload_h43k_note' => "
<ul>
   <li id='r4.3.1'><a href='#h4.3'><strong></strong></a> <a href='#r4.3.1'>4.3.1.</a> <strong>There are 4 2160p x265 encode slots for a given movie.</strong>
   <ul>
           <li id='r4.3.1.1'><a href='#r4.3.1'><strong></strong></a> <a href='#r4.3.1.1'>4.3.1.1.</a> <strong>Each group contains a CN Quality Slot, an EN Quality Slot, a Retention Slot and a Feature Slot. </strong>The Retention Slot should be more compressed, while the Quality Slot should be provided for the highest quality encode available. The Retention Slot should be at least 20% smaller than the Quality Slot to coexist.
           </li>
           <li id='r4.3.1.2'><a href='#r4.3.1'><strong></strong></a> <a href='#r4.3.1.2'>4.3.1.2.</a> <strong>A SDR release may occupy the Retention Slot defined by rule <a href='#r4.3.1.1'>4.3.1.1</a>, if provided enough comparison screenshots to prove the superiority over existing HD sources.</strong>
           </li>
       </ul>
   </li>
   <li id='r4.3.2'><a href='#h4.3'><strong></strong></a> <a href='#r4.3.2'>4.3.2.</a> See <a href='#h3.3'>relevant rules section</a> for more information on UHD uploads.
   </li>
</ul>",

    'upload_h44k_note' => "
<ul>
   <li id='r4.4.1'><a href='#h4.4'><strong></strong></a> <a href='#r4.4.1'>4.4.1.</a> <strong>One untouched NTSC DVD and one untouched PAL DVD are allowed. Both should be the highest quality source available under the staff's consideration.</strong>
   </li>
   <li id='r4.4.2'><a href='#h4.4'><strong></strong></a> <a href='#r4.4.2'>4.4.2.</a> <strong>One 720p untouched and one 720p remux are allowed. </strong>Both should be the highest quality source available under the staff's consideration.
   </li>
   <li id='r4.4.3'><a href='#h4.4'><strong></strong></a> <a href='#r4.4.3'>4.4.3.</a> <strong>One 1080p and one 2160p untouched, one 1080p and one 2160p DIY, one 1080p and one 2160p remux are allowed. </strong>They should be the highest quality source available under the staff's consideration.
   </li>
   <li id='r4.4.4'><a href='#h4.4'><strong></strong></a> <a href='#r4.4.4'>4.4.4.</a> See <a href='#h3.4'>relevant rules section</a> for more information on untouched uploads.
   </li>
</ul>",

    'upload_h45k_note' => "
<ul>
   <li id='r4.5.1'><a href='#h4.5'><strong></strong></a> <a href='#r4.5.1'>4.5.1.</a> <strong>One encode pack of extras per resolution (SD, 720p, 1080p plus remux) is allowed.</strong>
   </li>
   <li id='r4.5.2'><a href='#h4.5'><strong></strong></a> <a href='#r4.5.2'>4.5.2.</a> <strong>Extras packs from different editions can coexist if their content is actually different. </strong>If they are the same, the slot will be provided to the most complete pack.
   </li>
</ul>",
    'upload_h46k_note' => "
<ul>
   <li id='r4.6.1'><a href='#h4.6'><strong></strong></a> <a href='#r4.6.1'>4.6.1.</a> <strong>Each cut of a movie (e.g. Theatrical/Director's, Rated/Unrated, etc.) is allowed its own set of Slots. Give each type of HDR format torrents a set of standalone slots.</strong> <i class=\"u-colorWarning\">Update! 2021-08-06</i>
   </li>
   <li id='r4.6.2'><a href='#h4.6'><strong></strong></a> <a href='#r4.6.2'>4.6.2.</a> <strong>Mandarin dubbed non-Chinese uploads (Cantonese movies are Chinese movies) (contain both original Audio track and Mandarin dub track is better) enter Feature Slots. </strong>
   </li>
   <li id='r4.6.3'><a href='#h4.6'><strong></strong></a> <a href='#r4.6.3'>4.6.3.</a> <strong>English dubbed uploads (Dual Audio is preferred) of a non-English movie is considered a separate edition and allowed its own set of EN Quality slots.</strong> English dubbed uploads' subtitles may meet the requirements of CN Quality Slot, but they should join EN Quality Slots preferentially. <strong>Non-Mandarin Chinese film without both Mandarin dub and English dub is considered a separate edition and allowed its own set of CN Quality slots. </strong>They may meet the requirements of EN Quality Slot, but they should join CN Quality Slots preferentially. <i class=\"u-colorWarning\">Update! 2021-08-14</i>
   </li>
   <li id='r4.6.4'><a href='#h4.6'><strong></strong></a> <a href='#r4.6.4'>4.6.4.</a> <strong>While every upload should be sourced from what is considered the best edition/transfer for a given movie, a second set of slots may exceptionally be provided to uploads sourced from an inferior edition/transfer that provides an different viewing experience. </strong>This set will typically be composed of a single encode per resolution, a remux, and an untouched (no Retention Slot here). If you are not sure, please see <a href='forums.php?action=viewthread&threadid=21'>Can I upload this</a>. <i class=\"u-colorWarning\">Update! 2021-08-12</i>
   </li>
</ul>",

    'upload_h51k_note' => "
<ul>
   <li id='r5.1.1'><a href='#h5.1'><strong></strong></a> <a href='#r5.1.1'>5.1.1.</a> <strong>The usual trumping order for SD uploads is: VHS < TV < HDTV | WEB < DVD < Blu-ray. The usual trumping order for HD and UHD uploads is: HDTV < WEB | HD-DVD | Blu-ray.</strong>If there is a wide gap in bitrate, Blu-ray and HD-DVD sources  torrents can trump TV and HDTV sources in the same slot without any comparison screenshots.
       <ul>
           <li id='r5.1.1.1'><a href='#r5.1.1'><strong></strong></a> <a href='#r5.1.1.1'>5.1.1.1.</a> <strong>This trumping order is usually suitable but the decision ultimately comes down to quality (e.g. a WEB encode will not be deleted if Blu-ray sources turn out to be inferior).</strong>
           </li>
       </ul>
   </li>
   <li id='r5.1.2'><a href='#h5.1'><strong></strong></a> <a href='#r5.1.2'>5.1.2.</a> <strong>Untouched, non-stripped uploads can always trump equivalent uploads stripped of content such as extras or menus.</strong>
   </li>
</ul>",

    'upload_h52k_note' => "
<ul>
   <li id='r5.2.1'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.1'>5.2.1.</a> <strong>Any torrent that not met requirements of <a href='#h3'>the relevant rules section</a> can be trumped by preferred format torrents with a equivalent or superior quality.</strong>
       <ul>
           <li id='r5.2.1.1'><a href='#r5.2.1'><strong></strong></a> <a href='#r5.2.1.1'>5.2.1.1.</a> <strong>x264 (SD, HD) and x265 (UHD) are preferred encoders. </strong>H.264 or H.265 files of unknown lineage may occupy x264 or x265 slots defined by <a href='#r4.1.1'>4.1.1</a>, <a href='#r4.2.1'>4.2.1</a> and <a href='#r4.3.1'>4.3.1</a>, but will be more easily trumped by quality reasons.
           </li>
		   <li id='r5.2.1.1'><a href='#r5.2.1'><strong></strong></a> <strong>Dolby Vision remuxes and Chinese streaming site WEB-DL can use the MP4 container. </strong>Changing the container to MKV can not trump them. <i class=\"u-colorWarning\">New! 2021-08-14</i>
           </li>
       </ul>
   </li>
   <li id='r5.2.2'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.2'>5.2.2.</a> <strong>Uploads occupying Quality Slots defined by <a href='#r4.1.1.1'>4.1.1.1</a>, <a href='#r4.2.1.1'>4.2.1.1</a> and <a href='#r4.3.1.1'>4.3.1.1</a> can be trumped by significantly better quality encodes.</strong>
   </li>
   <li id='r5.2.3'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.3'>5.2.3.</a> <strong>Trumpable uploads are trumpable by uploads that fixed the issue pointed out by the mark. </strong>See <a href='#h5.4'>5.4</a> for a complete list of trumpable marks.
   </li>
   <li id='r5.2.4'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.4'>5.2.4.</a> <strong>Source type uploads (untouched, remuxes) will be effected from an aggressive trumping principles, sources with better viewing experiences can trump inferior ones.</strong>
   </li>
   <li id='r5.2.5'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.5'>5.2.5.</a> <strong>Quality trumps (for both encodes and sources) should provide enough screenshot comparisons demonstrating an improvement.</strong>
   </li>
   <li id='r5.2.6'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.6'>5.2.6.</a> <strong>In the case of a significant defect (e.g. incomplete, out of sync audio, wrong aspect ratio, etc.) a nuked Scene release is automatically trumpable by the REPACK or PROPER that replaces it. Scene releases nuked for reasons that don't affect the viewing experience (e.g. stolen source, dupe, naming error, etc.) are not trumpable.</strong>
   </li>
   <li id='r5.2.7'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.7'>5.2.7.</a> <strong>Remuxes may be trumped by equivalent but more complete uploads. The reasons for trumping are:</strong>
       <ul>
           <li id='r5.2.7.1'><a href='#r5.2.7'><strong></strong></a> <a href='#r5.2.7.1'>5.2.7.1.</a> Contained chapters if the previous remux didn't.
           </li>
           <li id='r5.2.7.2'><a href='#r5.2.7'><strong></strong></a> <a href='#r5.2.7.2'>5.2.7.2.</a> Added a commentary track or isolated score.
           </li>
           <li id='r5.2.7.3'><a href='#r5.2.7'><strong></strong></a> <a href='#r5.2.7.3'>5.2.7.3.</a> Replaced previous remuxes' PCM Audio tracks with suitable lossless equivalents.
           </li>
           <li id='r5.2.7.4'><a href='#r5.2.7'><strong></strong></a> <a href='#r5.2.7.4'>5.2.7.4.</a> Added a Chinese PGS/SUP subtitle if the previous remux didn't have one.
           </li>
           <li id='r5.2.7.5'><a href='#r5.2.7'><strong></strong></a> <a href='#r5.2.7.5'>5.2.7.5.</a> Unique master sourced material is allowed to coexist under the <strong>staff's approval</strong>.
           </li>
       </ul>
   </li>
   <li id='r5.2.8'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.8'>5.2.8.</a> <strong>Trumping priority for each slot is as follow:</strong>
       <ul>
           <li id='r5.2.8.1'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.1'>5.2.8.1.</a> <strong>Quality Slot: </strong>Which is splitted to CN and EN two slots, so the only consideration is encode quality. Uploader who plan to trump a previous torrent need to provide as more comparison screenshots as possible. Besides, there are Quality Slots without subtitle requirements (for Extras).
           </li>
           <li id='r5.2.8.2'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.2'>5.2.8.2.</a> <strong>Retention Slot: </strong>If the qualities are the same: no softcoded Chinese subtitle < containing a softcoded Chinese subtitle.
           </li>
           <li id='r5.2.8.3'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.3'>5.2.8.3.</a> <strong>Feature Slot: </strong>If the qualities are the same: containing a softcoded Chinese subtitle OR Mandarin dub < containing a softcoded Chinese subtitle AND Mandarin dub < containing a softcoded SPECIAL EFFECTS Chinese subtitle and Mandarin dub.
               <ul>
                   <li id='r5.2.8.3.1'><a href='#r5.2.8.3'><strong></strong></a> <a href='#r5.2.8.3.1'>5.2.8.3.1.</a> <strong>Mandarin dub: </strong>no Mandarin dub < Taiwan Mandarin dub < Mainland Mandarin dub.
                   </li>
                   <li id='r5.2.8.3.2'><a href='#r5.2.8.3'><strong></strong></a> <a href='#r5.2.8.3.2'>5.2.8.3.2.</a> <strong>Quality: </strong>The bitrate of the video track should be higher than scene uploads. The bitrate of newer uploads which wants to trump should be 15% higher than scene uploads.
                   </li>
               </ul>
           </li>
           <li id='r5.2.8.4'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.4'>5.2.8.4.</a> <strong>Remux Slot: </strong>ordinary source without Chinese subtitle < ordinary source with softcoded Chinese subtitle < excellent source without Chinese subtitle < excellent source with softcoded Chinese subtitle.
           </li>
           <li id='r5.2.8.5'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.5'>5.2.8.5.</a> <strong>Untouched Slot: </strong>ordinary source without Chinese subtitle < ordinary source with softcoded Chinese subtitle < excellent source without Chinese subtitle < excellent source with softcoded Chinese subtitle.
           </li>
           <li id='r5.2.8.6'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.6'>5.2.8.6.</a> <strong>DIY Slot: </strong>no softcoded Chinese subtitle < containing softcoded Chinese subtitle < containing softcoded special effects Chinese subtitle < containing softcoded Chinese subtitle + Mandarin dub < containing softcoded special effects Chinese subtitle + Mandarin dub.
           </li>
       </ul>
   </li>
   <li id='r5.2.9'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.9'>5.2.9.</a> <strong>Trumping priority for comment tracks of non-Chinese movies:</strong>
       <ul>
           <li id='r5.2.9.1'><a href='#r5.2.9'><strong></strong></a> <a href='#r5.2.9.1'>5.2.9.1.</a> <strong>Remux Slot: </strong>If the sources are the same and both have a main track Chinese subtitle: no subtitle < English comment subtitle < Chinese comment subtitle.
           </li>
           <li id='r5.2.9.2'><a href='#r5.2.9'><strong></strong></a> <a href='#r5.2.9.2'>5.2.9.2.</a> <strong>DIY Slot: </strong>If the sources are the same and both have a main track Chinese subtitle: no subtitle < English comment subtitle < Chinese comment subtitle.
           </li>
       </ul>
   </li>
   <li id='r5.2.10'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.10'>5.2.10.</a> <strong>Fixing dirty lines, banding, blocking, etc. is not considered as absolute trumping reason.</strong>
   </li>
</ul>",

    'upload_h53k_note' => "
<ul>
   <li id='r5.3.1'><a href='#h5.3'><strong></strong></a> <a href='#r5.3.1'>5.3.1.</a> <strong>Any torrent that has been inactive for over 4 weeks is trumpable.</strong>
   </li>
   <li id='r5.3.2'><a href='#h5.3'><strong></strong></a> <a href='#r5.3.2'>5.3.2.</a> <strong>Any new upload that is left unseeded for 24 hours is trumpable.</strong>
   </li>
   <li id='r5.3.3'><a href='#h5.3'><strong></strong></a> <a href='#r5.3.3'>5.3.3.</a> <strong>Reseed inactive torrents rather than trump them if possible.</strong>
   </li>
</ul>",

    'upload_h54k_note' => "<ul>
   <li id='r5.4.1'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.1'>5.4.1.</a> <strong>Bad Aspect Ratio: </strong>An encoding error is the reason why uploads displayed at an improper aspect ratio.
   </li>
   <li id='r5.4.2'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.2'>5.4.2.</a> <strong>Non-OAR: </strong>This upload has an aspect ratio different than the original, theatrically presented movie. Once a release with proper aspect ratio is available, no non-OAR upload may coexist in the same resolution group.
   </li>
   <li id='r5.4.3'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.3'>5.4.3.</a> <strong>Bloated：</strong>The video or audio bitrate of this upload is too high. A handy sheet for maximum audio bitrates: <i class=\"u-colorWarning\">Update! 2021-08-06</i>
   <table class='Table TableRuleAudio'>
   <tr class='Table-rowHeader'>
       <td class='Table-cell' colspan='2' rowspan='2'>Source Audio</td>
       <td class='Table-cell' colspan='4'>Encode</td>
       <td class='Table-cell' colspan='2'>Remux</td>
   </tr>
   <tr class='Table-rowHeader'>
       <td class='Table-cell'>SD</td>
       <td class='Table-cell'>720p</td>
       <td class='Table-cell'>1080p</td>
       <td class='Table-cell'>2160p</td>
       <td class='Table-cell'>1080p</td>
       <td class='Table-cell'>2160p</td>
   </tr>
   <tr class='Table-row'>
       <td class='Table-cell' rowspan='3'>Primary Track</td>
       <td class='Table-cell'>7.1/5.1 lossless</td>
       <td class='Table-cell'>640 kbps AC3 (448 kbps AC3 preferred)</td>
       <td class='Table-cell'>1509 kbps DTS (640  kbps AC3 preferred)</td>
       <td class='Table-cell'>1536 kbps E-AC3</td>
       <td class='Table-cell'>as-is</td>
       <td class='Table-cell'>as-is</td>
       <td class='Table-cell'>as-is</td>
   </tr>
   <tr class='Table-row'>
       <td class='Table-cell'>2.0/1.0 lossless</td>
       <td class='Table-cell'>16-bit FLAC (high quality AAC preferred)</td>
       <td class='Table-cell'>16-bit FLAC (high quality AAC preferred)</td>
       <td class='Table-cell'>16-bit FLAC</td>
       <td class='Table-cell'>as-is (24-bit FLAC preferred)</td>
       <td class='Table-cell'>as-is (FLAC preferred)</td>
       <td class='Table-cell'>as-is (FLAC preferred)</td>
   </tr>
   <tr class='Table-row'>
       <td class='Table-cell'>Lossy</td>
       <td class='Table-cell'>as-is</td>
       <td class='Table-cell'>as-is</td>
       <td class='Table-cell'>as-is</td>
       <td class='Table-cell'>as-is</td>
       <td class='Table-cell'>as-is</td>
       <td class='Table-cell'>as-is</td>
   </tr>
   <tr class='Table-row'>
       <td class='Table-cell' rowspan='2'>Secondary Track</td>
       <td class='Table-cell'>lossless</td>
       <td class='Table-cell'>medium quality AAC</td>
       <td class='Table-cell'>high quality AAC</td>
       <td class='Table-cell'>16-bit FLAC</td>
       <td class='Table-cell'>16-bit FLAC</td>
       <td class='Table-cell'>as-is (FLAC preferred)</td>
       <td class='Table-cell'>as-is (FLAC preferred)</td>
   </tr>
   <tr class='Table-row'>
       <td class='Table-cell'>lossy</td>
       <td class='Table-cell'>medium quality AAC or as-is</td>
       <td class='Table-cell'>medium quality AAC or as-is</td>
       <td class='Table-cell'>medium quality AAC or as-is</td>
       <td class='Table-cell'>medium quality AAC or as-is</td>
       <td class='Table-cell'>as-is</td>
       <td class='Table-cell'>as-is</td>
   </tr>
</table>
   </li>
   <li id='r5.4.4'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.4'>5.4.4.</a> <strong>Redundant Audio Track(s): not for Untouched Slots and DIY Slots. </strong>This upload includes superfluous audio tracks such as non-English dubs, or redundant versions of the same track. <a href='#r4.0.4'>4.0.4.</a> Mandarin dubs in dialect (including Cantonese) movies are not considered as redundant.
   </li>
   <li id='r5.4.5'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.5'>5.4.5.</a> <strong>Deinterlacing Issues: </strong>This upload has been improperly deinterlaced.
   </li>
   <li id='r5.4.6'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.6'>5.4.6.</a> <strong>Improper Framerate: </strong>This upload plays at a framerate different than the native, proper framerate.
   </li>
   <li id='r5.4.7'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.7'>5.4.7.</a> <strong>Improperly Synchronized Subtitles: </strong>Subtitles contained with this upload are usable, but not properly synchronized.
   </li>
   <li id='r5.4.8'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.8'>5.4.8.</a> <strong>Improper Codec/Container: </strong>This upload does not conform to our <a href='#h3'>preferred formats</a>.
   </li>
   <li id='r5.4.9'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.9'>5.4.9.</a> <strong>Non-Conform Resolution: </strong>This upload does not conform to our <a href='#h3'>preferred resolutions</a>.
   </li>
   <li id='r5.4.10'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.10'>5.4.10.</a> <strong>Inferior Source: </strong>This source does not provide the best viewing experience currently available.
   </li>
   <li id='r5.4.11'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.11'>5.4.11.</a> <strong>Low Quality: </strong>This upload was encoded from a particularly poor source, or suffers from major quality issues.
   </li>
   <li id='r5.4.12'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.12'>5.4.12.</a> <strong>Playback Issues: </strong>Issues usually detailed by a second mark are preventing this upload from being perfectly played back or encoded from.
   </li>
   <li id='r5.4.13'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.13'>5.4.13.</a> <strong>Incomplete: </strong>This upload is lacking content, in a way usually detailed by a second mark.
   </li>
   <li id='r5.4.14'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.14'>5.4.14.</a> <strong>Lack of Basic Subtitle: only for EN Quality Slot, Retention Slot, Remux Slot and Untouched Slot. </strong>A non-English movie does not contain Chinese and English subtitles (internal or external) will be marked. The mark can be canceled by uploading external required subtitles. Silent films or so will not be marked.
        <ul>
            <li id='r5.4.14.1'><a href='#r5.4.14'><strong></strong></a> <a href='#r5.4.14.1'>5.4.14.1.</a> <strong>EN Quality Slot:</strong> The mark can be canceled by uploading external English subtitles.
            </li>
            <li id='r5.4.14.2'><a href='#r5.4.14'><strong></strong></a> <a href='#r5.4.14.2'>5.4.14.2.</a> <strong>Other three types: </strong>The mark can be canceled by uploading external Chinese/English subtitles. This kind of torrents can be trumped by equal or better quality torrents with softcoded Chinese subtitles.
            </li>
        </ul>
   </li>
   <li id='r5.4.15'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.15'>5.4.15.</a> <strong>No Forced English Subtitles: only for EN Quality Slot. </strong>This upload does not include separate English subtitles for significant non-English dialogue.
   </li>
   <li id='r5.4.16'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.16'>5.4.16.</a> <strong>Non-original audio track: </strong>when a film has no original audio track, Mandarin dub and English dub at the same time (only other small language dubs included), this mark would be applied. <i class=\"u-colorWarning\">Update! 2021-08-14</i>
   </li>
   <li id='r5.4.17'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.17'>5.4.17.</a> <strong>Out of Sync. Audio: </strong>Audio contained with this upload is usable, but not properly synchronized.
   </li>
   <li id='r5.4.18'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.18'>5.4.18.</a> <strong>Poor Cropping: </strong>This upload was significantly overcropped or undercropped.
   </li>
   <li id='r5.4.19'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.19'>5.4.19.</a> <strong>Poorly Translated Subtitles: </strong>Subtitles included with this upload are poor quality and not an accurate translation of the movie.
   </li>
   <li id='r5.4.20'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.20'>5.4.20.</a> <strong>Hardcoded Subtitles: </strong>Subtitles have been hardcoded in the video track of this upload. Hardcoded forced subtitles are not targeted by this mark.
   </li>
   <li id='r5.4.21'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.21'>5.4.21.</a> <strong>Transcoded Audio: </strong>The audio track included with this upload was transcoded from an already compressed, lossy source.
   </li>
   <li id='r5.4.22'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.22'>5.4.22.</a> <strong>Watermarked: </strong>This upload is watermarked in a significant way.
   </li>
   <li id='r5.4.23'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.23'>5.4.23.</a> <strong>Upscale: </strong>This upload was encoded from a low resolution source.
   </li>
   <li id='r5.4.24'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.24'>5.4.24.</a> <strong>Dead: </strong>This upload has not been seeded for at least 4 weeks. This mark is appended and removed automatically.
   </li>
   <li id='r5.4.25'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.25'>5.4.25.</a> <strong>Redundant File(s): </strong>This upload included files that not relevant. See <a href='#r2.1.3'>2.1.3</a>. <i class=\"u-colorWarning\">New! 2021-12-31</i>
</ul>",




    'chat_title' => "Chat",
    'chat_general' => "General Rules",
    'chat_general_rules' => "<ul>
    <li>If a staff member says stop and you continue, expect at least to be banned from the chatting room.</li>
    <li>Obsessive annoyance — both to other users and staff — will not be tolerated. Don't comment others casually.</li>
    <li>No advertising, spamming, abusing, discriminating, etc..</li>
    <li>No prejudice, especially related to race, religion, politics, sexual preference, ethnic background, etc. It is highly suggested to avoid these subjects entirely.</li>
    <li>Staff have the final decision.</li>
    </ul>",
    'chat_forum' => "Anything not allowed on the forums is also not allowed on IRC and vice versa. They are
        separated for convenience only.",
    'chat_forums' => "Forums Rules",
    'chat_forums_rules' => "<li>Read the rule of the forum before posting.</li>
        <li>Do not post irrelevant thread. Invite threads posted on non-invite forums will cause a big trouble.</li>
        <li>Do not obtain economic benefits through non-trading forum posts.</li>
        <li>Do not inappropriately advertise your uploads.</li>
        <li>Refrain from quoting excessively. When quoting someone, use only the portion of the quote that is absolutely necessary. This includes quoting pictures!</li>
        <li>Mature content in posts must be properly tagged. The correct format is as follows: <strong>[mature=description] ...content... [/mature]</strong>, where \"description\" is a mandatory description of the post contents. Misleading or inadequate descriptions will be penalized. <a href='/staff.php'>Ask staff</a> first if you are not sure.</li>",
    'chat_groups' => "Chatting Rules",
    'chat_forums_irc' => "<li>Do not belittle or slander any other tracker on any occasion under the jurisdiction of ${site_name}.</li>
        <li>Everyone began from a greenhand. Please help new users patiently if possible.</li>",

    'tags_title' => "Tagging",
    'tags_summary' => "<li>Tags should be comma-separated, and you should use a period (\".\") to separate words inside
        a tag. Such as \"<strong class='u-colorSuccess'>sci.fi</strong>\" or \"<strong
            class='u-colorSuccess'>post.rock</strong>\".</li>
    <li>You can select from the dropdown box on <a href=\"upload.php\" target=\"_blank\">the torrent upload page</a>. We
        recommend adding more specific sub-genres manually, but it is fine to use only these official tags if you do not
        feel qualified to be more specific. <strong>Please note that the \"<strong
                class='u-colorSuccess'>2000s</strong>\" tag refers to music produced between 2000 and 2009.</strong>
    </li>
    <li>Do not add 'useless' tags, such as personal opinions or anything you specify elsewhere on the form. \"<strong
            class='u-colorWarning'>seen.live</strong>\", \"<strong class='u-colorWarning'>awesome</strong>\", \"<strong
            class='u-colorWarning'>kung.fu</strong>\" (containing in \"<strong
            class='u-colorSuccess'>action</strong>\") etc.. If an upload contains live contents, you can tag it as \"<strong
            class='u-colorSuccess'>live</strong>\".</li>
    <li>Only tag information on the movie itself&#8202;&mdash;&#8202;<strong>not the individual release</strong>. Tags
        such as \"<strong class='u-colorWarning'>remux</strong>\", \"<strong class='u-colorWarning'>encode</strong>\",
        \"<strong class='u-colorWarning'>blu.ray</strong>\", \"<strong class='u-colorWarning'>eac3to</strong>\", etc.
        are strictly forbidden. Remember that these tags will be used for other versions of the same movie.</li>
    <li>If you have questions about the dropdown box on <a href=\"upload.php\" target=\"_blank\">the torrent upload page</a>, please don't add the tag.</li>",

    'tags_summary_onupload' => "<li>Tags should be comma-separated, and you should use a period (\".\") to separate
        words inside a tag. Such as \"<strong class='u-colorSuccess'>hip.hop</strong>\" or \"<strong
            class='u-colorSuccess'>post.rock</strong>\".</li>
    <li>There is a list of official tags you can select from the dropdown box above. We recommend adding more specific
        sub-genres manually, but it is fine to use only these official tags if you do not feel qualified to be more
        specific. <strong>Please note that the \"<strong class='u-colorSuccess'>2000s</strong>\" tag refers to music
            produced between 2000 and 2009.</strong></li>
    <li>Avoid abbreviations if at all possible. So instead of tagging an album as \"<strong
            class='u-colorWarning'>alt.rock</strong>\" or \"<strong class='u-colorWarning'>prog</strong>\", tag it as
        \"<strong class='u-colorSuccess'>alternative.rock</strong>\" or \"<strong
            class='u-colorSuccess'>progressive.rock</strong>\". Make sure that you use correct spelling.</li>
    <li>Do not add 'useless' tags, such as personal opinions or anything you specify elsewhere on the form. \"<strong
            class='u-colorWarning'>seen.live</strong>\", \"<strong class='u-colorWarning'>awesome</strong>\", \"<strong
            class='u-colorWarning'>flac</strong>\", etc. If an album contains live contents, you can tag it as \"<strong
            class='u-colorSuccess'>live</strong>\".</li>
    <li>Only tag information on the album itself&#8202;&mdash;&#8202;<strong>not the individual release</strong>. Tags
        such as \"<strong class='u-colorWarning'>v0</strong>\", \"<strong class='u-colorWarning'>eac</strong>\",
        \"<strong class='u-colorWarning'>vinyl</strong>\", \"<strong class='u-colorWarning'>from.what</strong>\", etc.
        are strictly forbidden. Remember that these tags will be used for other versions of the same album.</li>
    <li>Do not use \"<strong class='u-colorWarning'>hardcore</strong>\" or \"<strong
            class='u-colorWarning'>garage</strong>\". These tags are too vague as both describe two wholly unrelated
        genres. Use \"<strong class='u-colorSuccess'>hardcore.dance</strong>\", \"<strong
            class='u-colorSuccess'>hardcore.punk</strong>\", \"<strong
            class='u-colorSuccess'>garage.rock</strong>\" or \"<strong
            class='u-colorSuccess'>garage.house</strong>\" instead.</li>
    <li>For full guidelines on good tagging practice including more types that you should not add, please read the <a
            href='wiki.php?action=article&id=42' target='_blank'>Tagging Rules</a>.</li>",

    'upload_title_de' => "This is the section of the rules regarding any and all content which is allowed on this
    site.",
    'clients_title_de' => " These are the clients we allow to connect to our tracker and rules specific to them.",
    'chat_title_de' => "Read this before posting in our forums or talking on our IRC network.",
    'tags_title_de' => "These rules govern what tags can and cannot be added.",
    'collages_title_de' => "These are the rules that govern collages.",
    'requests_title_de' => "These are the rules that govern requests.",
    'ratio_title_de' => "These are the rules for seeding/leeching activity on this site.",
    'golden_rules_de' => "These are the most important rules. Breaking these rules will result in the most serious
    consequences.",




    'end' => "分享率规则"
);
