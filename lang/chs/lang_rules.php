<?php
$site_name = SITE_NAME;
$staffpm = '<a href="staffpm.php">Staff PM</a>';
$lang_rules = array(
    'rules' => "规则",
    'type' => "分类",
    'info' => "注释",
    'golden_rules' => "黄金规则",
    'golden_rules_used' => "黄金规则适用于 ${site_name} 和我们的社交网络。这是最高级的规则，如不遵守，你的账号将会遭受最严厉的惩罚。",
    'short_11' => "禁止重复注册。",
    'long_11' => "每位用户终生仅允许拥有一个账号，如果你的账号被禁，你可以通过 <a href=\"" . TG_DISBALE_CHANNEL . "\" target='_blank'>Telegram 官方群</a>或 ${staffpm} 联系管理员。不要创建多个账号（即俗称的 “马甲”）。一经查实所有关联账号都将被封禁。",
    'short_12' => "禁止交易、出售、赠与或提供账号。",
    'long_12' => "如果你想要封存账号，可以通过 ${staffpm} 联系管理员来停用你的账号。",
    'short_13' => "禁止共享账号。",
    'long_13' => "账号仅供私人使用。禁止以任何方式（例如共享登录信息、外部程序等）将你的账号访问权限授予他人。如果你的亲朋好友想要使用本站，请 <a href=\"wiki.php?action=article&name=invite\">邀请</a> 或引导他们加入我们的 <a href=\"" . TG_GROUP . "\" target='_blank'>Telegram 官方群</a>。",
    'short_14' => "不要让你的账号处于非活动状态。",
    'long_14' => "为保证账号处于良好状况，你应定期登录 ${site_name}。如果做不到，你的账号会被禁用，详情请查看 <a href=\"wiki.php?action=article&id=11\">不活跃账号</a> 一文。",
    'short_21' => "不要邀请低质量用户。",
    'long_21' => "你需要对你邀请的用户负责。你邀请的用户达不到合格分享率，不会导致你受罚。但若你邀请的用户违反了黄金规则，你的账号或相关权限可能会被禁用。",
    'short_22' => "禁止交易、出售、以及公开地赠送或提供邀请。",
    'long_22' => "只邀请你认识和信任的人，若非如此，请不要邀请他们。请尽可能邀请你现实中认识的朋友，若要邀请网友，你应该确定认识并相信他们。不要在未设置浏览限制的 PT 站内论坛板块、贴吧、论坛、聊天软件群（QQ、TG、微信等，私聊除外）、社交媒体或任何公共场所提供和回应求邀请求。例外情况：管理组指定的专员可以在被批准的场所提供邀请。注意不要让发邀帖被搜索引擎抓到。",
    'short_23' => "禁止随处求邀。",
    'long_23' => "在升级到 Power User 后可访问 ${site_name} 的邀请相关板块。在求邀前必须先行阅读 <a href=\"forums.php?action=viewthread&threadid=15\">求邀区版规</a> 和 <a href=\"forums.php?action=viewthread&threadid=100\">禁止求邀列表</a>。如果其他用户不曾表示可以提供邀请，则你不能以任何方式向其求邀，包括私信。",
    'short_24' => "禁止公开泄露站点信息",
    'long_24' => "不要在任何公共区域泄露站点真实名称和简称、服务器地址以及 Tracker 地址，截图时请遮蔽站点 Logo。",
    'short_31' => "禁止参与分享率作弊。",
    'long_31' => "通过使用 BitTorrent 协议或站点功能的漏洞（例如滥用 <a href=\"rules.php?p=requests\">求种</a>）来伪造发布／下载量或修改分享率数据是被绝对禁止的。如有疑问，请 ${staffpm} 以了解详情。",
    'short_32' => "禁止向 Tracker 上报非正常统计数据（即禁止作弊）。",
    'long_32' => "禁止向 Tracker 报告错误数据，无论你使用的是否是 “能作弊的客户端” ，或者白名单内的客户端。",
    'short_33' => "禁止使用未允许的客户端。",
    'long_33' => "本站 Tracker 采用 <a href=\"rules.php?p=clients\">白名单</a> 模式，仅允许使用白名单内的客户端，魔改版是不被允许的，测试版或 CVS 版请私信管理员。",
    'short_34' => "禁止修改 ${site_name} 的种子文件。",
    'long_34' => "将非 ${site_name} 的 Tracker URL 加入到 ${site_name} 种子的行为是被禁止的。这样做会产生错误数据并被视作作弊。无论种子是否正在客户端内做种都适用此规则。",
    'short_35' => "禁止将种子文件或密钥分享给他人。",
    'long_35' => "每个 ${site_name} 的种子文件中都嵌入了包含你个人密钥的 URL，密钥使用户能够向 Tracker 报告数据，密钥泄露将有可能使他人有机会窃取你的分享率。",
    'short_41' => "禁止威胁、社工、敲诈用户及管理员。",
    'long_41' => "禁止以任何理由公开曝光用户及管理员的隐私信息，或以此类行为相要挟。隐私信息包括但不限于个人识别信息（例如姓名、记录、活动日志细节、照片）。未经许可，不得讨论或共享未经用户自愿公开提供的信息。包括通过搜集已公开信息（如谷歌搜索结果）来获取私人信息。",
    'short_42' => "禁止欺诈。",
    'long_42' => "禁止任何形式的诈骗（如网络钓鱼）。",
    'short_43' => "尊重管理组的决定。",
    'long_43' => "只允许与 Moderator 私下讨论分歧。如果 Moderator 已退休或联系不上，你可以 ${staffpm}。不允许因个人理由联系多位 Moderator；但是，如果你需要第三方意见，可以联系 Administrator。联系管理员的方式包括私信、${staffpm} 和<a href=\"" . TG_GROUP . "\" target='_blank'>Telegram 官方群</a>。",
    'short_44' => "禁止冒充工作人员。",
    'long_44' => "禁止在站内、站外或交流群冒充管理员或官方服务账号。也禁止歪曲管理员的决定。",
    'short_45' => "禁止网络霸凌。",
    'long_45' => " “网络霸凌” 是指对其他用户的指手画脚的行为。禁止对立、挑衅或攻击涉嫌违反规则的用户及被举报的用户。如果你发现违规行为，举报就够了。",
    'short_46' => "不要要求优惠活动。",
    'long_46' => "优惠活动（如种子免费、候选通过等）由管理员自行决定。它们不遵循固定的安排，用户不得提出类似要求。",
    'short_47' => "禁止收集用户识别信息。",
    'long_47' => "禁止使用 ${site_name} 的服务并通过脚本、漏洞或其他技术来获取任何类型的用户识别信息（如 IP 地址、个人链接等）。",
    'short_48' => "禁止利用 ${site_name} 的服务（包括 Tracker、网站和交流群）来牟取商业利益。",
    'long_48' => "禁止将 ${site_name} 提供的服务（例如 Gazelle、Ocelot）及维护的代码商业化。禁止通过利用上述服务（如用户种子数据等）商业化 ${site_name} 用户提供的资源。其他推广、募捐及交易行为也被禁止。",
    'short_51' => "禁止使用免费的代理或 VPN 浏览 ${site_name}。",
    'long_51' => "禁止通过公用或免费的代理、VPN、Tor 浏览网站，你可以通过付费 VPN、私有服务器（盒子）和代理来浏览网站。不允许通过 Tor 网络访问站点或连接 Tracker 服务器。最多允许 3 个 IP 同时做种。如有疑问请发送 ${staffpm}。<i class=\"u-colorWarning\">Update! 2021-06-23</i>",
    'short_52' => "禁止滥用自动访问网站。",
    'long_52' => "所有自动化站点访问都必须通过指定的 <a href=\"https://github.com/WhatCD/Gazelle/wiki/JSON-API-Documentation\">API</a> 完成。API 在 10 秒内只会回应 5 个请求。 脚本和其他自动化流程不得收集网站的 HTML 页面。如有疑问，请咨询管理员。",
    'short_53' => "禁止自动抓取免费种子。",
    'long_53' => "禁止使用自动化的方式（例如，基于 API 的脚本、日志或站点抓取等）自动抓取免费种子。详情请参阅 ${site_name} 的 <a href=\"wiki.php?action=article&id=63\">免费种子自动收集政策</a> 一文。",
    'short_61' => "禁止寻找或利用现有的 BUG。",
    'long_61' => "禁止在站点中实时寻找或利用 BUG（你可以在本地开发环境试验）。如果你发现了严重错误或安全漏洞，请立即按照 ${site_name} 的 <a href=\"wiki.php?action=article&id=64\">漏洞报告政策</a> 进行报告。也可以在 <a href=\"forums.php?action=viewforum&forumid=16\">论坛的反馈版块</a> 报告不太严重的 BUG。",
    'short_62' => "禁止公布漏洞。",
    'long_62' => "有关漏洞的公布、组织、传播、分享、技术讨论或技术促进等事宜皆由管理组决定。漏洞被定义为对内部、外部、非营利或营利性服务的意料之外或未被许可的利用。漏洞的类型可能随时被重新划分。",
    'short_70' => "尊重所有管理组成员。",
    'long_70' => "${site_name} 的工作人员是志愿者，他们将私人时间用于维护网站运行，而这是没有任何补偿的。不尊重他们可能会导致被警告甚至更严重的后果。",
    'short_71' => "管理组对规则拥有最终解释权。",
    'long_71' => " ${site_name} 的所有规则可能会有不同的解释。鉴于管理组编写了这些规则，他们拥有最终解释权。如果你对本文感到疑惑或不解，或者你认为应该重新制定规则，请发送 ${staffpm}。",

    'ratio_title' => "分享率与 H&R",
    'ratio' => "分享率",
    'hnr' => "H&R",
    'ratio_used' => "概述",
    'ratio_summary_a' => "你的<strong>分享率</strong>等于你上传量除以下载量的商。你可以在站点页面的最上方或者你个人信息的 “统计” 部分看到。",
    'ratio_summary_b' => "为了能够享有<strong>下载种子的权限</strong>，你的分享率必须保持在某一最小值之上。这个最小值就是你的<strong>合格分享率</strong>。",
    'ratio_summary_c' => "如果你当前的分享率低于你的合格分享率，你将会有两周的时间来使之高于你的合格分享率。在这期间，你将被列入<strong>分享率监控名单</strong>。",
    'ratio_summary_d' => "如果在给定时间内，你没有将分享率提高到合格分享率以上，你将失去下载权限，即无法下载更多的资源。但你仍然可以进行除了下载以外的正常活动。",
    'ratio_used_a' => "合格分享率概述",
    'ratio_summary_a_a' => "合格分享率就是你必须维持的最低分享率，否则你会被列入分享率监控名单。你可以在站点最上方的 “合格分享率” 后边看到，或者在个人信息的 “统计” 部分看到。",
    'ratio_summary_b_b' => "合格分享率因人而异。每个用户的合格分享率是由其账号流量数据计算得来的。",
    'ratio_summary_c_c' => "你的合格分享率是根据以下两点计算得来的：（1）你已完成的全部种子数量；（2）你当前的总做种数。",
    'ratio_summary_d_d' => "总做种数包括你完成下载的种子和你已发布的种子。",
    'ratio_summary_e_e' => "随着你做种比率的增加，系统会将合格分享率降低。你的做种率越高，你需要达到的合格分享率就越低，进而你就不容易被列入分享率监控名单。",
    'ratio_table' => "分享率规则",
    'ratio_dl' => "用户下载量",
    'ratio_dl_title' => "这些单位是二进制而非十进制的，举个例子，1 GB 中有 1024 MB。",
    'ratio_re_0' => "合格分享率（0% 做种）",
    'ratio_re_100' => "合格分享率（100% 做种）",
    'ratio_sum' => "合格分享率计算：",
    'ratio_1' => "<strong>1. 计算合格分享率可能的最大值和最小值</strong>。使用上述表格，在第一列中查看你账号下载量所在的范围。接下来，查看相邻一列的数值。第二列给出了每个下载量对应的最大合格分享率，此时对应 0% 做种率的情形。第三列给出了每个下载资源量对应的最小合格分享率，对应 100% 做种率的情形。",
    'ratio_2' => "<strong>2. 计算实际的合格分享率</strong>。你的实际合格分享率数值将处于最大值和最小值之间。为了计算你的实际合格分享率，系统会首先将最大合格分享率与数值 [1-(做种数/完成数)] 相乘。更直观的表达式如下所示：",
    'ratio_show' => "<li>说明：在上述公式中，<var>完成数</var>表示你已下载完成的且未被系统删除的种子数量。如果同一个种子下载两次，公式中只会计算一次。如果下载完成的种子被站点删除了，它就不会被计算在上式之内。
		</li>
					<li>在上述公式中，<var>做种数</var>是你过去一周做种时间超过 72 小时的平均做种数量。如果一个种子在过去的一周内做种时间不足 72 小时，它将不会计入你的做种数量。请注意，尽管做种数有可能大于完成数，你的做种率最高仍不会超过 100%。
		</li>",

    'ratio_3' => "<strong>3. 如有必要，在上述步骤中得到的值会四舍五入到你的最低合格分享率中</strong>。这是因为，当做种数等于完成数时，上述公式计算返回的值为 0，但对于大多数账号而言，最低合格分享率要大于 0。",
    'ratio_summary_1' => "合格分享率详解：",
    'ratio_summary_1_con' => "<li>如果你超过一周没有做种，你的合格分享率就会变为最高分享率。一旦你重新开始做种并持续 72 小时，根据上述公式，你的合格分享率就会降低。
</li>
			<li>如果下载量低于 5 GB，你不会被列入分享率监控名单且不会被要求达到某个合格分享率。在此情况下，无论做种比例如何，你的合格分享率都是 0。
</li>
			<li>如果你的下载量低于 20 GB 且做种数等于完成数，你的合格分享率将会为 0。
            </li>
			<li>随着你下载量的增加，你的最小和最大合格分享率会逐渐接近。当你的下载量达到 100GB 时，这两个数值会相等。即当用户下载量大于等于 100GB 后，其最小合格分享率将会恒为 0.6。
</li>",

    'ratio_summary_2' => "合格分享率举例：",
    'ratio_summary_2_con' => "<li>比如，张三下载了 25 GB 资源，通过查询上述表格得知，该数值位于 20~30 GB 范围之间。张三的最大合格分享率是 0.30，最小合格分享率是 0.05。
</li>
			<li>而张三下载完成了 90 个种子，当前正在做种的有 45 个种子。为了计算张三的实际合格分享率，我们将他的最大合格分享率 0.3 乘以[1-(做种数/完成数)]，即:
				<samp>0.30 × [1 &minus; (45 / 90)] = 0.15</samp>
</li>
			<li>计算得出的合格分享率为 0.15，处于最大值 0.30 和最小值 0.05 之间。</li>
			<li>若网站所显示的张三的合格分享率大于上述计算值，那是由于在过去一周内，他所做种的 45 个种子尚未达到 72 小时。在这种情况下，系统不会将做种数认定为 45。
</li>",

    'ratio_summary_3' => "分享率监控总则：",
    'ratio_summary_3_con' => "<li>在分享率监控启动之前，每个用户都可以下载 5 GB 资源。</li>
			<li>如果你已经下载了 5 GB 以上资源且你的分享率未达到合格分享率，你将会被列入分享率监控名单，你将有<strong>两周</strong>时间来改善你的分享率，使之高于合格分享率。
</li>
			<li>当你处于分享率监控名单时又下载了 10 GB 资源，你的下载权限将会被自动禁用。</li>
			<li>如果在两周之内你无法脱离分享率监控名单，你将会失去下载权限。此后，你将无法下载更多资源。你的账号仍然可以登录。
</li>
			<li>分享率监控系统是自动运行的，无法被管理员手动干预。</li>",

    'ratio_summary_4' => "脱离分享率监控名单：",
    'ratio_summary_4_con' => "<li>为了脱离分享率监控名单，你必须通过发布更多的资源来提高你的分享率，或者通过提高做种数来降低你的合格分享率。要脱离分享率监控名单，你的分享率必须大于等于合格分享率。</li>
			<li>如果在分享率监控期限结束时，你未能提高你的分享率，你将会失去下载权限，你的合格分享率会被临时设定为可能的最高分享率（如同你 0% 做种率的情形）。
</li>
			<li>因此，失去下载权限后，要恢复合格分享率至原来的水平使其反应你的实际做种率，你需要再次在一周时间内做种 72 小时以上。当达到 72 小时后，合格分享率就会更新，并反映你当前的做种数量，这一点与有下载权限的用户一样。</li>
			<li>一旦你的分享率大于等于合格分享率，下载权限就会被恢复。</li>",
    'hnr_rules_body' => "<strong>什么是 H&R：</strong><br/><ul>
        <li>H&R，全称 Hit and Run，中文译作 “下完就跑”，指的是下载完成种子后在规定时间内单种分享率不达标或做种时长不达标的行为。一个种子不达标一次，即计为一个 H&R。</li>
        <li>在下载一个种子总大小 20% 的数据量后，你就需要满足做种要求，以免积累 H&R。要求的具体内容是，在两周内累计做种达到 48 小时；或是单种分享率达到 1。</li>
        <li>一旦你积累的 H&R 总数达到 10 个，你的下载权限就会被暂时封禁。你可以通过辅种或 “<a href='bonus.php'>破财消灾</a>” 的方式消除你的 H&R 数量。</li>
        <li>由于 H&R 的统计由站点 Tracker 所收到的数据决定，因此，只关注你的客户端数据是不够的，为确保安全，我们建议你在 <a href='torrents.php?type=downloaded'>记录页面</a> 确认已达到做种要求后再撤种。</li>
        <li>进一步解释请见 <a href='wiki.php?action=article&id=68'>H&R 常见问题解答</a>。</li>
    </ul>",




    'requests_title' => "求种",
    'requests_summary' => "<li><strong>不要求违规种子。</strong>遵守求种规则是你的责任与义务。若不守规矩，你的求种会被删，且已支付的上传量不会退还给你。
</li>
			<li><strong>一个求种一部影视作品。</strong>在一个求种中提请多部电影（例如成龙作品全集）抑或是含糊不清的求种都是不被允许的。你可能想要多种格式，但你不能全都要。举个例子，你可能需要原盘和 Encode 两种，但你不能同时选择它们。你也可能提出了某个导演多部电影的请求，但这个请求可以被该导演的某部电影应求。
</li>
			<li><strong>不要因过分挑剔而否决应求。</strong>如果你没有在求种中明确提出你的精细要求（比如比特率或特定版本），你就不能否决应求并随后更改求种描述。不要因为你的无知否决应求（比如应求的种子可能是转码后的资源但你搞不清楚）。在此种情况下，你可以向一线支持求助。当应求种子确实没有满足你已经阐释清除的要求时，你可以否决该应求。
</li>
			<li><strong>应求面前，人人平等。</strong>上传量交易是不被允许的。通过滥用求种系统来为其他用户牟取便利是不可饶恕的，包括为特定用户量身定制求种（无论是否在求种中写明）。我们严厉禁止模糊化求种要求，而后否决其他人的应求以使某个特定用户能够应求。如被举报，无论是求种者还是被 “钦定” 的应求者都会被警告并扣除该求种相应的上传量。
</li>
			<li><strong>禁止要求求种者上调求种报酬。</strong>上传量报酬是对助人为乐的奖赏——而不是赎买。任何求种者不加价就不应求的用户将会面临严厉的惩罚。
</li>",


    'collages_title' => "合集",
    'collages_summary' => "<li>合集的用途并非记录某个导演或演员的所有影视作品。因为我们已经为艺人提供了单独的页面来跟踪记录这些信息。</li>
        <li>理想情况下，合计应至少包含 3 部电影。如果暂时还没有，那么必须保证它具有成长性。</li>
        <li>破坏合集的行为会被严惩，导致编辑合集的权限被剥夺（最轻处罚）。</li>
        <li>任何 “最爱” 类合集须引自可靠的来源，比如一位受人尊敬的批评家、制片人、演员或刊物。除非是你的私人合集（达到一定用户等级方可创建），否则你不能创建一个你的个人最爱合集。</li>
        <li>合集须关注这些方面：类型、制片公司、获奖／提名作品、系列电影，或是其他可量化的、能够将一组电影归集在一起的概念（例如：恐怖片重拍、关于中东战争的电影等）。</li>
        <li>在自建新合集前请务必先查询类似的合集是否已经存在。</li>
        <li>在将电影加入合集前，请确保它与合集的主题、合集信息中所写的要求相适应。</li>
        <li>正确且描写性地命名你的合集，并为它撰写一段恰当且提供有效信息的主旨介绍：这个合集是关于什么的，你创建这个合集的依据是什么。</li>
        <li>管理组成员可以锁定合集。上锁的合集要么已经定型，要么是定期会更新。如果你认为一个上锁的合集缺了电影或存在其他问题，请报告它。</li>
        <li>你如果对某部合集是否符合上述规则不太肯定，请在创建前发送 <a href='staff.php'>Staff PM</a> 询问管理组。</li>",


    'clients_title' => "客户端",
    'clients_list' => "客户端白名单",
    'clients_summary' => "客户端规则是维持我们群体正直诚实的保障。它保证了我们能够将具有破坏性和欺骗性的客户端（比如迅雷）拒之门外，因为这些东西会破坏我们 Tracker 的正常运行、损害我们用户的利益。</br></br>
    <strong><a href='https://github.com/c0re100/qBittorrent-Enhanced-Edition/releases'>修改版客户端</a> 可能会导致数据统计错误，使用它会导致你被警告，乃至禁用账号，请务必使用官方三位数版本号的客户端。</strong>",


    'upload_title' => "发布",
    'upload_rules' => "发布规则",
    'upload_search' => "输入关键词",
    'upload_search_note' => "示例：搜索 <strong>高清</strong> 得到与 <strong>高清</strong> 相关的规则。搜索词 <strong>高清</strong> + <strong>替代</strong> 得到所有与 <strong>高清</strong> 和 <strong>替代</strong> 相关的规则。",

    'upload_h1k' => "发布什么",
    'upload_h11k' => "允许内容",
    'upload_h12k' => "特别禁止",
    'upload_h13k' => "Scene 发布",
    'upload_h13k_a' => "<a href='wiki.php?action=article&amp;id=140'>Scene</a> 发布",

    'upload_h2k' => "必需信息",
    'upload_h21k' => "命名",
    'upload_h22k_t' => "种子描述",
    'upload_h22k' => "种子描述",
    'upload_r220' => "总览",
    'upload_r220_note' => "这张图表是重复和替代规则的总览。",
    'upload_h23k_t' => "电影海报",
    'upload_h23k' => "电影海报：你必须为你的电影提供一张封面图（例如电影海报、VHS 或 DVD 封面）。尽你所能搜索封面，但若是一无所得，则包含片名的一张截图也可。",
    'upload_h24k_t' => "其他发行信息",
    'upload_h24k' => "其他发行信息：任何你在发布页面填写的内容应与资源本身相符。",

    'upload_h3k' => "格式说明",
    'upload_h31k' => "标清（SD）",
    'upload_h32k' => "高清（HD）",
    'upload_h33k' => "超高清（UHD）",
    'upload_h34k' => "原盘",
    'upload_h35k' => "附加内容",
    'upload_h36k' => "外挂字幕",

    'upload_h4k' => "共存",
    'upload_h40k' => "总览",
    'upload_h41k' => "标清",
    'upload_h42k' => "高清",
    'upload_h43k' => "超高清",
    'upload_h44k' => "原盘",
    'upload_h45k' => "附加内容",
    'upload_h46k' => "其他",

    'upload_h5k' => "替代",
    'upload_h51k' => "源",
    'upload_h52k' => "质量",
    'upload_h53k' => "不活跃",
    'upload_h54k_t' => "可替代标记",
    'upload_h54k' => "可替代标记：这些标记会附加在任何未达到我们标准的种子上。",

    'upload_h6k' => "其他",
    'upload_h61k' => "不要发布你没有完全访问权限的种子或内容。无论是在本地还是在盒子，你都必须在制作种子并发布之前拥有内容的完全处置权。",
    'upload_h62k' => "不要发布你不打算做种的种子。本站要求你为所有的种子在两周内做种至少 48 小时，或直至你的分享率达到 1，即输出了一个完整的副本。即使你是种子的发布者，此规则也同样适用。参见 <a href='rules.php?p=ratio'>HnR 规则</a> 了解更多。",
    'upload_h63k' => "尽你所能地长期做种。本站旨在成为为所有电影、所有规格所设立的永久的档案馆，你做种越久，我们离梦想就越近，你的分享率也越好看。尽量不要让做种率达到底线值成为你的习惯，你应对自己有所要求。",
    'upload_h64k' => "做种时请考虑自身状况，你正在做种的每个种子都应该是可以被顺利下载的，即使慢一些。如果你的网络连接速度很慢，请放缓发种速度以保证新的下载者能连得上。不要故意将带宽限制到无法正常上传与下载的速度。",

    'upload_introk' => "介绍",
    'upload_introk_note' => "<p>为保证资源质量，下面的发布规则繁多且详细。为清楚和彻底地解释规则，我们认为这个长度是必要的。每条规则的摘要在其详细说明之前以<span style='font-weight: bold;'>粗体</span>显示，以便于阅读。你还可以在索引中找到相应的规则部分。序号前的 “↑” （返回至 <a href='#Index'>目录</a>）和 <a href='#Index'>规则链接</a>（跳转至详细说明）可助你快速导航。</p>
    <p>在发布任何内容之前，如果你仍然不确定规则的含义，请在站内寻求支持：<a href='staff.php'>一线支持</a>、<a href='forums.php?action=viewforum&amp;forumid=16'>论坛咨询</a> 或在 <a href='wiki.php?action=article&amp;name=IRC'><?= BOT_HELP_CHAN ?>IRC（建设中）</a> 上提问。如果以上未能为你提供足够的帮助，请 <a href='staffpm.php'>私信管理</a>。如果你在发布规则中发现任何失效的链接，请 <a href='staffpm.php'>私信管理</a>，并在你的信息中包含发布规则编号（例如 <a href='#r2.4.3'>2.4.3</a>）。</p>",
    'upload_h11k_note' => "<ul><li id='r1.1.1'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.1'>1.1.1.</a>
                 <strong>长片：</strong>长片指的是任意时长大于 45 分钟的电影。如果某部电影于短片而言太长，于长片而言又太短，请查询 <a href='https://imdb.com/' target='_blank'>IMDb</a>。
                </li>
                <li id='r1.1.2'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.2'>1.1.2.</a>
                    <strong>短片：</strong>简而言之就是短于长片的电影。其时长范围从数秒到大约 45 分钟不等。
                </li>
                <li id='r1.1.3'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.3'>1.1.3.</a>
                <strong>单口喜剧：</strong>单口相声演员的电影形式表演。无论时长长短，都归属于此类。发布非演员官方发行的任何表演都属于违规行为。
                </li>
                <li id='r1.1.4'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.4'>1.1.4.</a>
                    <strong>迷你剧集：</strong>迷你剧集是一类持续在电视上每次以单集形式播放的剧情片或纪录片。它不是电视连续剧，因为它在计划播完的剧集之后并无续集或下一季。如果你不确定是否可以发布，请在发布前 <a href='forums.php?action=viewthread&threadid=21'>申请许可</a>。
                </li>
                <li id='r1.1.5'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.5'>1.1.5.</a> <strong>诗选剧：</strong>诗选剧指的是为电视制作的一种剧集类型，每一集剧情都是完全独立的长片或短片。诗选剧的每一集都必须单独发布。集间故事情节有关联，但布景和／或演员每季都有变更的 “迷你剧系列” 是不允许的。请在发布前 <a href='forums.php?action=viewthread&threadid=21'>申请许可</a>。
                </li>
                <li id='r1.1.6'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.6'>1.1.6.</a>
                    <strong>纪录片系列剧：</strong>若与 <a href='#r1.1.4'>1.1.4</a> 相似，即每一季拥有一个总的主题（如 BBC 的《蓝色星球》第一二季）；或与 <a href='#r1.1.5'>1.1.5</a> 相似，即每一集的情节相互独立（如 ESPN 的《30 for 30》），则纪录片系列剧也允许发布。如果你不确定是否可以发布，请在发布前 <a href='forums.php?action=viewthread&threadid=21'>申请许可</a>。
                </li>
                <li id='r1.1.7'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.7'>1.1.7.</a>
                    <strong>现场表演：</strong>任何官方发行的音乐会、演艺、剧院演出录像，或任何介于它们之间的内容。禁止发布盗录视频或抓取的直播视频流。
                </li>
                <li id='r1.1.8'><a href='#h1.1'><strong></strong></a> <a href='#r1.1.8'>1.1.8.</a>
                    <strong>电影集：</strong>当且仅当套盒或合辑中的多部电影共用光盘且无法分离时，这类原盘允许发布。这也包括单张光盘上的多部电影。Encode 及 REMUX 作品则必须分开发布。
                </li></ul>",
    'upload_h12k_note' => "<ul>
                    <li id='r1.2.1'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.1'>1.2.1.</a>
                    <strong>预售：</strong>任何预售（包括但不限于 CAM、TS、TC、R5、DVDScr）都是不允许的。
                </li>
                <li id='r1.2.2'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.2'>1.2.2.</a>
                    <strong>电视节目：</strong>禁止电视节目或电视连续剧。这不包括为电视制作的电影或规则 <a href='#r1.1.4'>1.1.4</a>、<a href='#r1.1.5'>1.1.5</a> 和 <a href='#r1.1.6'>1.1.6</a> 中定义的管理批准的迷你剧集和诗选剧。
                </li>
                <li id='r1.2.3'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.3'>1.2.3.</a>
                    <strong>色情：</strong>本站不允许任何被 IMDb 添加成人标签的爱情动作片或电影。如果你觉得一部电影被打上成人标签并不公正，请在发布前 <a href='forums.php?action=viewthread&threadid=21'>申请许可</a>。
                </li>
                <li id='r1.2.4'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.4'>1.2.4.</a>
                    <strong>MV 集锦：</strong>它们不是完整长度的音乐会、纪录片或短片。
                </li>
                <li id='r1.2.5'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.5'>1.2.5.</a>
                    <strong>体育视频：</strong>禁止棒球比赛、摔跤比赛、汽车比赛、极限运动剪辑等。有关体育的纪录片是允许的。如果你不太清楚其中的区别，请在发布前 <a href='forums.php?action=viewthread&threadid=21'>申请许可</a>。
                </li>
                <li id='r1.2.6'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.6'>1.2.6.</a>
                    <strong>影迷剪辑：</strong>只允许官方发行的内容或电影。
                </li>
                <li id='r1.2.7'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.7'>1.2.7.</a>
                    <strong>视频教程：</strong>任何类型的教学和培训视频都是不允许的。电影制作相关的内容须在发布前 <a href='forums.php?action=viewthread&threadid=21'>申请许可</a>。
                </li>
                <li id='r1.2.8'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.8'>1.2.8.</a>
                    <strong>非视频种子：</strong>在任何情况下，你的种子都应包含视频文件，但不允许压缩包格式（RAR、ZIP……）。
                </li>
                <li id='r1.2.9'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.9'>1.2.9.</a>
                    <strong>打包电影：</strong>一个种子一部电影。包含多部电影的套盒只能原封不动地发布。有关发布套盒的更多信息，参见规则 <a href='#r1.1.4'>1.1.8</a>。
                </li>
                <li id='r1.2.10'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.10'>1.2.10.</a>
                    <strong>电影与附加内容合在一起：</strong>附加内容必须与电影本体分开发布，除非原封不动地发布原盘。
                </li>
                <li id='r1.2.11'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.11'>1.2.11.</a>
                    <strong>不允许劣质转码：</strong>所有 Rip 使用的源都必须是完整的原盘或原始视频流，即禁止二压。（这包括 BRRip 和经过二压的 DVD5）。
                </li>
                <li id='r1.2.12'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.12'>1.2.12.</a>
                    <strong>低质量出品：</strong>目前的名单：aXXo、BRrip、CM8、CrEwSaDe、DNL、EVO (WEB-DL 允许)、FaNGDiNG0、FRDS (Remux 允许)、HD2DVD、HDTime、iPlanet、KiNGDOM、Leffe、mHD、mSD、nHD、nikt0、nSD、NhaNc3、PRODJi、RDN、SANTi、 STUTTERSHIT、TERMiNAL (低比特率 UHD)、ViSION、WAF、x0r、YIFY、PSP/iPad/移动设备预设 Encode。
                </li>
                <li id='r1.2.13'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.13'>1.2.13.</a>
                    <strong>预告片集锦：</strong>不允许预告片集锦。
                </li>
                <li id='r1.2.14'><a href='#h1.2'><strong></strong></a> <a href='#r1.2.14'>1.2.14.</a>
                    <strong>特别禁止内容：</strong>不允许发布任何罗列在我们 <a><a href='torrents.php?action=do_not_upload_movie_list'>黑名单</a></s>（建设中）上的内容。<strong>禁止广告内容</strong>（包括但不限于封装时在视频音频标题处写入的推广链接、种子说明内包含的大尺寸推广图等）。<i class=\"u-colorWarning\">Update! 2021-08-06</i>
                </li></ul>",

    'upload_h21k_note' => "<ul>

                <li id='r2.1.1'><a href='#h2.1'><strong></strong></a> <a href='#r2.1.1'>2.1.1.</a> <strong>文件（夹）名必须使用电影原始语种名称或官方英文名（推荐）。（如海报所示等的官方英文名，其优先级高于 IMDb。）</strong>
                    <ul>
                    <li id='r2.1.1.1'><a href='#r2.1.1'><strong></strong></a> <a href='#r2.1.1.1'>2.1.1.1.</a> Internal Remux 的文件名需要在影片标题后包含（格式或顺序不限）原始发行年、分辨率，以及音视频编码（例如 The.Thing.1982.1080p.AVC.DTS-HD.MA，或 Citizen Kane (1941) 1080p H264 FLAC）。</li>
                    <li id='r2.1.1.2'><a href='#r2.1.1'><strong></strong></a> <a href='#r2.1.1.2'>2.1.1.2.</a> 文件（夹）名中如包含与资源本身无关的无意义内容，如序号、个人标记等的种子，会被标为 “可替代”。例：“2. 无敌浩克”。</li>
                    <li id='r2.1.1.3'><a href='#r2.1.1'><strong></strong></a> <a href='#r2.1.1.3'>2.1.1.3.</a> 对于占据了 <a href='#r4.0.1'>4.0.1</a> 中所定义中字槽的种子，如果文件（夹）名中带有非中／英字符，则会被标为 “可替代”。</li>
                    </ul>
                </li>
                <li id='r2.1.2'><a href='#h2.1'><strong></strong></a> <a href='#r2.1.2'>2.1.2.</a> <strong>压制组发行（来自 P2P 组或 Scene 组）不应重命名，</strong>除非它们不满足规则 <a href='#r2.1.1'>2.1.1</a> 或我们的文件名要求。
                </li>
                <li id='r2.1.3'><a href='#h2.1'><strong></strong></a> <a href='#r2.1.3'>2.1.3.</a> <strong>保持文件夹内文件尽可能简洁。</strong>不要包含：样片、截图、desktop.ini/thumbs.db 文件或其他任何与你要发布的内容不完全相关的东西，否则将被标记为 “可替代”。与复制过程相关的文件允许放在 DVD/BD 的目录结构中。考虑到辅种的便利性，请不要在种子内包含字幕文件，而是 <a href='subtitles.php'>单独上传</a>。<i class=\"u-colorWarning\">Update! 2021-06-23</i>
                </li>
                <li id='r2.1.4'><a href='#h2.1'><strong></strong></a> <a href='#r2.1.4'>2.1.4.</a> <strong>DVD 和 BD 文件目录结构不允许改动，仅顶层文件夹允许重命名。</strong>
                </li>
            </ul>",
    'upload_h22k_note' => "<ul>
                <li id='r2.2.1'><a href='#h2.2'><strong></strong></a> <a href='#r2.2.1'>2.2.1.</a> <strong>截图：在发布页面的 “种子描述” 中，要求至少三张与影片分辨率相同的 PNG 格式的截图。截图应存放在 <a href='upload.php?action=image'>官方图床</a>。</strong>此外，使用 <a href='https://ptpimg.me'>ptpimg.me</a>、<a href='https://pixhost.to'>pixhost.to</a>、<a href='https://yes.ilikeshots.club/'>yes.ilikeshots.club</a>、<a href='https://imgbox.com'>imgbox.com</a> 或 <a href='https://img.pterclub.com'>猫站</a> 图床的外链也可以。对于剧集类种子，你需要为每一集各提供至少一张截图。
                </li>
                <li id='r2.2.2'><a href='#h2.2'><strong></strong></a> <a href='#r2.2.2'>2.2.2.</a> <strong>Mediainfo：你必须使用 MediaInfo 或用于蓝光原盘的 BDInfo 提供所发布内容的规格。如果一个种子包含了多个视频文件，则应为每个文件都提供视频 Encode 信息。不得编辑 MediaInfo 日志。</strong>如果你确定它不对，请通过报告提交必要的修正。
                </li>
                <li id='r2.2.3'><a href='#h2.2'><strong></strong></a> <a href='#r2.2.3'>2.2.3.</a> <strong>禁止在种子或种子描述中打广告。</strong>文件名、文件夹名带有的压制组组名不算广告。
                </li>
            </ul>",

    'upload_h23k_note' => "
            <ul>
                <li id='r2.3.1'><a href='#h2.3'><strong></strong></a> <a href='#r2.3.1'>2.3.1.</a> <strong>能获取到官方艺术海报时就不允许影迷自制的作品。</strong>
                </li>
                <li id='r2.3.2'><a href='#h2.3'><strong></strong></a> <a href='#r2.3.2'>2.3.2.</a> <strong>影院海报相对而言是首选，且不允许实体碟的照片。</strong>
                </li>
                <li id='r2.3.3'><a href='#h2.3'><strong></strong></a> <a href='#r2.3.3'>2.3.3.</a> <strong>对于此类图片的存放，要求同截图，见规则 <a href='#r2.2.1'>2.2.1</a>。</strong>
                </li>
            </ul>",
    'upload_h24k_note' => "
            <ul>
                <li id='r2.4.1'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.1'>2.4.1.</a> <strong>但凡你想发布资源的 IMDb 链接存在，填写它就是必须的。</strong>如果 IMDb 中对于电影缺少梗概，请考虑花点时间亲自写上。
                <ul>
                        <li id='r2.4.1.1'><a href='#r2.4.1'><strong></strong></a> <a href='#r2.4.1.1'>2.4.1.1.</a> <strong>在发布音乐会时，影片描述中必须带有完整的曲目列表，IMDb 链接（如果存在）或是零售链接（比如亚马逊）也要有。</strong>
                        </li>
                    </ul>
                </li>
                <li id='r2.4.2'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.2'>2.4.2.</a> <strong>如果你要发布的电影版本与在影院上映的原始版本不同（导演剪辑、未分级、配音……），请勾选 “版本信息” 并挑选合适的标签。</strong>如果特殊功能有适用的标签（HDR10、Dolby Vision、Dolby Atmos、3D、2in1），也必须添加。完整的标签列表见 <a href='wiki.php?action=article&id=2'>此处</a>。
                </li>
                <li id='r2.4.3'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.3'>2.4.3.</a> <strong>如果你正在发布你自己的 Encode 或 Rip 作品，则应勾选 “自制”。</strong>
                </li>
                <li id='r2.4.4'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.4'>2.4.4.</a> <strong>对于所有的影片，字幕信息都是必填项。</strong>
                </li>
                <li id='r2.4.5'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.5'>2.4.5.</a> <strong>如果你发布的资源有任何可在来源站获取到的相关信息（例如源、注释、x264 日志……），则你必须将之填入种子描述。</strong>如果是你自制的影片，提供这些信息也是我们鼓励的。
                </li>
                <li id='r2.4.6'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.6'>2.4.6.</a> <strong>你给电影添加的标签应是客观的描述。</strong>IMDb 标签是权威的，而不可信的（主观的、有政治倾向的）标签则会被删除。标签应用于标志宽泛的类型（例如 drama 或 sci.fi），而非具体的已经有记录或更适用于合集的东西（例如 steven.spielberg、korean、imdb.top.250 等）。
                </li>
                <li id='r2.4.7'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.7'>2.4.7.</a> <strong>如有条件，请花点时间添加预告片，或是任何其他能够促使用户下载你种子的信息。</strong>小心不要造成剧透。
                </li>
                <li id='r2.4.8'><a href='#h2.4'><strong></strong></a> <a href='#r2.4.8'>2.4.8.</a> <strong>原种内针对种子的制作说明、压制日志、对比图等描述是重要的反映制作者对一部作品的用心见证，转载时应尽详保留而非简单地按最低描述要求转载。</strong>
                </li>
            </ul>",

    'upload_h31k_note' => "
            <ul>
                <li id='r3.1.1'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.1'>3.1.1.</a> <strong>标清种子指的是任何未达到高清要求的种子（见规则 <a href='#r3.2.1'>3.2.1</a>）。</strong>
                <ul>
                        <li id='r3.1.1.1'><a href='#r3.1.1'><strong></strong></a> <a href='#r3.1.1.1'>3.1.1.1.</a> <strong>来自标清源的 x264 Encode 在任何情况下都不允许再放大，且应根据其存储分辨率添加标签。</strong>
                        </li>
                        <li id='r3.1.1.2'><a href='#r3.1.1'><strong></strong></a> <a href='#r3.1.1.2'>3.1.1.2.</a> <strong>来自高清和超高清源的 x264 Encode 必须使用 480p（最大分辨率为 854×480 像素）或 576p（最大分辨率为 1024×576 像素）分辨率。</strong>
                        </li>
                    </ul>
                </li>
                <li id='r3.1.2'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.2'>3.1.2.</a> <strong>SD Encode 必须使用 x264 编码和 MKV 容器。</strong>
                </li>
                <li id='r3.1.3'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.3'>3.1.3.</a> <strong>在电影找不到未劣质转码的首选格式时，错误的编解码器、容器和分辨率也许可以容忍。</strong>如果电影能获取到正确格式，则不能再发布错误格式的，除非存在明显的质量提升。见相关的 <a href='#h4.1'>共存</a>／<a href='#h5.2'>替代</a> 规则或到 <a href='forums.php?action=viewthread&threadid=22'>这里</a> 询问例外情况。
                </li>
                <li id='r3.1.4'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.4'>3.1.4.</a> <strong>不允许以 Encode 作品为源再次进行压制。</strong>
                </li>
                <li id='r3.1.5'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.5'>3.1.5.</a> <strong>一部影片下，如已存在 720p 的画面更好的蓝光 Encode，则该组内所有标清 Encode 将被删除。</strong>同理，若站点已存在更好的 720p 蓝光 Encode，则禁止再发布标清 Encode。例外：如 DVD 内容与蓝光存在实质性差异，则允许 DVD Encode 与 720p 蓝光 Encode 共存。或者你认为手中的标清 Encode 具有<strong>特别的价值</strong>，请移步 <a href='forums.php?action=viewthread&threadid=21'>我能发布它吗</a>。<i class=\"u-colorWarning\">Update! 2021-08-06</i>
                </li>
                <li id='r3.1.6'><a href='#h3.1'><strong></strong></a> <a href='#r3.1.6'>3.1.6.</a> 更多关于标清共存的信息，参见 <a href='#h4.1'>相关规则</a>。
                </li>
            </ul>",
    'upload_h32k_note' => "
            <ul>
                <li id='r3.2.1'><a href='#h3.2'><strong></strong></a> <a href='#r3.2.1'>3.2.1.</a> <strong>允许的分辨率有 720p（最大分辨率为 1280×720 像素）和 1080p（最大分辨率为 1920×1080p）。</strong>
                </li>
                <li id='r3.2.2'><a href='#h3.2'><strong></strong></a> <a href='#r3.2.2'>3.2.2.</a> <strong>HD Encode 必须使用 x264 编码和 MKV 容器。</strong>（允许 HDR x265 1080p Encode，详见 <a href='#r4.2.2'>4.2.2</a>。）
                </li>
                <li id='r3.2.3'><a href='#h3.2'><strong></strong></a> <a href='#r3.2.3'>3.2.3.</a> <strong>在电影找不到未劣质转码的首选格式时，错误的编解码器、容器和分辨率也许可以容忍。</strong>如果电影能获取到正确格式，则不能再发布错误格式的，除非存在明显的质量提升。见相关的 <a href='#h4.1'>共存</a>／<a href='#h5.2'>替代</a> 规则或到 <a href='forums.php?action=viewthread&threadid=22'>这里</a> 询问例外情况。
                </li>
                <li id='r3.2.4'><a href='#h3.2'><strong></strong></a> <a href='#r3.2.4'>3.2.4.</a> <strong>高清 Encode 必须源自 Blu-ray、HD-DVD、HDTV 或 WEB。</strong>任何其他源都应 <a href='forums.php?action=viewthread&threadid=21'>由管理批准</a>。
                </li>
                <li id='r3.2.5'><a href='#h3.2'><strong></strong></a> <a href='#r3.2.5'>3.2.5.</a> 更多关于高清共存的信息，参见 <a href='#h4.2'>相关规则</a>。
                </li>
            </ul>",

    'upload_h33k_note' => "
            <ul>
                <li id='r3.3.1'><a href='#h3.3'><strong></strong></a> <a href='#r3.3.1'>3.3.1.</a> <strong>允许的分辨率是 2160p（最大分辨率为 4096×2160 像素）。</strong>
                </li>
                <li id='r3.3.2'><a href='#h3.3'><strong></strong></a> <a href='#r3.3.2'>3.3.2.</a> <strong>HDR (High Dynamic Range) 超高清源必须在编码时保持此特性。</strong>
                </li>
                <li id='r3.3.3'><a href='#h3.3'><strong></strong></a> <a href='#r3.3.3'>3.3.3.</a> <strong>超高清 Encode 必须使用 x265 编码和 MKV 容器。Web 源超高清 SDR 允许使用 x264 编码。</strong><i class=\"u-colorWarning\">Update! 2021-08-06</i>
                <ul>
                        <li id='r3.3.3.1'><a href='#r3.3.3'><strong></strong></a> <a href='#r3.3.3.1'>3.3.3.1.</a> <strong>SDR 超高清 Encode 若被规则 <a href='#r4.3.1.2'>4.3.1.2</a> 允许，则可使用 x264 编码。</strong>
                        </li>
                    </ul>
                </li>
                <li id='r3.3.4'><a href='#h3.3'><strong></strong></a> <a href='#r3.3.4'>3.3.4.</a> <strong>在电影找不到未劣质转码的首选格式时，错误的编解码器、容器和分辨率也许可以容忍。</strong>如果电影能获取到正确格式，则不能再发布错误格式的，除非存在明显的质量提升。见相关的 <a href='#h4.1'>共存</a>／<a href='#h5.2'>替代</a> 规则或到 <a href='forums.php?action=viewthread&threadid=22'>这里</a> 询问例外情况。
                </li>
                <li id='r3.3.5'><a href='#h3.3'><strong></strong></a> <a href='#r3.3.5'>3.3.5.</a> 更多关于超高清共存的信息，参见 <a href='#h4.3'>相关规则</a>。
                </li>
            </ul>",

    'upload_h34k_note' => "
            <ul>
                <li id='r3.4.1'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.1'>3.4.1.</a> <strong>原盘种子是与零售光盘完全一致的副本。</strong>它们可能含有菜单、附加内容以及额外的音轨（完整的 VOB_IFO/M2TS 副本）或是被删减到只剩下电影主体（仅 HD 和 UHD Remux）。Scene NFO 应添加到发布页面的 NFO 区域。仅版权警告部分可被从完整的 VOB_IFO/M2TS 副本中删减掉。
                <ul>
                    <li id='r3.4.1.1'><a href='#r3.4.1'><strong></strong></a> <a href='#r3.4.1.1'>3.4.1.1.</a> <strong>防拷贝和地区锁必须被移除。</strong>
                        </li>
                    </ul>
                </li>
                <li id='r3.4.2'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.2'>3.4.2.</a> <strong>DVD 原盘必须使用 VOB_IFO 容器（VIDEO_TS 文件夹和内容）。</strong>
                <ul>
                    <li id='r3.4.2.1'><a href='#r3.4.2'><strong></strong></a> <a href='#r3.4.2.1'>3.4.2.1.</a> DVD5 存在单碟最大 4.37 GiB 的限制。DVD9 存在单碟最大 7.95 GiB 的限制。
                        </li>
                    </ul>
                </li>
                <li id='r3.4.3'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.3'>3.4.3.</a> <strong>HDTV 原始抓流必须使用 TS 或 MKV 容器。</strong>
                </li>
                <li id='r3.4.4'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.4'>3.4.4.</a> <strong>蓝光原盘和 DIY 原盘应使用 M2TS 或 ISO 容器。</strong>
                <ul>
                    <li id='r3.4.4.1'><a href='#r3.4.4'><strong></strong></a> <a href='#r3.4.4.1'>3.4.4.1.</a> BD25 存在单碟最大 23.28 GiB 的限制。BD50 存在单碟最大 46.57 GiB 的限制。BD66 存在单碟最大 61.47 GiB 的限制。BD100 存在单碟最大 93.13 GiB 的限制。
                    </li>
                </ul>
                </li>
                <li id='r3.4.5'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.5'>3.4.5.</a> <strong>蓝光 Remux 必须使用 MKV 容器。</strong>Remux 种子由原始（或无损压缩）的音视频组成，简单地混流在一起即可。
                <ul>
                    <li id='r3.4.5.1'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.1'>3.4.5.1.</a> <strong>Remux 必须始终使用从源光盘能获取到的最优质量轨道。</strong>
                    </li>
                    <li id='r3.4.5.2'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.2'>3.4.5.2.</a> <strong>Remux 必须以下列顺序混流：视频 - 主音轨 (标为默认) - 次音轨 - 字幕</strong>
                    </li>
                    <li id='r3.4.5.3'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.3'>3.4.5.3.</a> <strong>2.0 及以下的 PCM 和 DTS-HD MA 的音轨必须转码到 FLAC，请勿将 24 bit DTS-HD MA 转换成 16 bit。2.1 及以上 PCM 必须转码到 DTS-HD MA 或 FLAC。
                    </strong>
                    </li>
                    <li id='r3.4.5.4'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.4'>3.4.5.4.</a> <strong>Remux 允许 SRT 字幕。</strong>
                    </li>
                    <li id='r3.4.5.5'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.5'>3.4.5.5.</a> <strong>杜比视界 Remux 可以 MP4 容器的形式存在。</strong>详见<a href='#r5.2.1.2'>5.2.1.2</a> 。
                    </li>
                    <li id='r3.4.5.6'><a href='#r3.4.5'><strong></strong></a> <a href='#r3.4.5.6'>3.4.5.6.</a> <strong>Remux 如勾选 “自制”，须提供 eac3to log。</strong>转载的 Remux 如有可能，也建议贴上。
                    </li>
                </ul>
                </li>
                <li id='r3.4.6'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.6'>3.4.6.</a> <strong>仅包含附加内容的原盘必须与主碟一起制成单个种子发布。</strong>
                </li>
                <li id='r3.4.7'><a href='#h3.4'><strong></strong></a> <a href='#r3.4.7'>3.4.7.</a> 更多关于原盘种子共存的信息，参见 <a href='#h4.4'>相关规则</a>。
                </li>
            </ul>",

    'upload_h35k_note' => "
            <ul>
                <li id='r3.5.1'><a href='#h3.5'><strong></strong></a> <a href='#r3.5.1'>3.5.1.</a> <strong>附加内容是包含在电影官方发行中的视频材料，但不是电影主体的任何一个版本（幕后花絮、采访……）。</strong>
                <ul>
                        <li id='r3.5.1.1'><a href='#r3.5.1'><strong></strong></a> <a href='#r3.5.1.1'>3.5.1.1.</a> <strong>附加内容种子必须在发布页面勾选 “非电影主体” 选项以标记。</strong>
                        </li>
                    </ul>
                </li>
                <li id='r3.5.2'><a href='#h3.5'><strong></strong></a> <a href='#r3.5.2'>3.5.2.</a> <strong>附加内容仅在其包含于任一官方零售发行的完整版时允许发布。</strong>
                <ul>
                        <li id='r3.5.2.1'><a href='#r3.5.2'><strong></strong></a> <a href='#r3.5.2.1'>3.5.2.1.</a> <strong>附加内容必须以发行商／版本指明来源，见规则 <a href='#r2.5.2'>2.5.2</a> 以了解更多关于版本信息的内容。</strong>
                        </li>
                    </ul>
                </li>
                <li id='r3.5.3'><a href='#h3.5'><strong></strong></a> <a href='#r3.5.3'>3.5.3.</a> <strong>仅包含附加内容的光盘必须与主体光盘制成同一个种子一起发布。</strong>
                </li>
                <li id='r3.5.4'><a href='#h3.5'><strong></strong></a> <a href='#r3.5.4'>3.5.4.</a> <strong>拥有 IMDb 页面的附加内容必须单独发布。</strong>
                </li>
                <li id='r3.5.5'><a href='#h3.5'><strong></strong></a> <a href='#r3.5.5'>3.5.5.</a> 更多关于附加内容种子共存的信息，参见 <a href='#h4.5'>相关规则</a>。
                </li>
            </ul>",
    'upload_h36k_note' => "
            <ul>
                <li id='r3.6.1'><a href='#h3.6'><strong></strong></a> <a href='#r3.6.1'>3.6.1.</a> <strong>用户上传的字幕应与对应种子的视频文件同步，否则会被直接删除。</strong>
                </li>
                <li id='r3.6.2'><a href='#h3.6'><strong></strong></a> <a href='#r3.6.2'>3.6.2.</a> <strong>站点允许上传的字幕格式有 .sub、.idx、.sup、.srt、.vtt、.ass、.smi、.ssa。</strong>此外，也允许压缩打包上传，支持 .rar、.zip、.7z、.tar、.tgz、.tar.gz。
                </li>
                <li id='r3.6.3'><a href='#h3.6'><strong></strong></a> <a href='#r3.6.3'>3.6.3.</a> <strong>字幕文件建议采用与对应视频文件相一致的文件命名以方便使用。</strong>你也可以在尾部增加用以标明语言的字段，如 “Monsters.Inc.2001.1080p.BluRay.DTS.x264.D-Z0N3.chs.srt”。
                </li>
                <li id='r3.6.4'><a href='#h3.6'><strong></strong></a> <a href='#r3.6.4'>3.6.4.</a> <strong>字幕文件应以 Unicode 编码为佳。</strong>
                </li>
                <li id='r3.6.5'><a href='#h3.6'><strong></strong></a> <a href='#r3.6.5'>3.6.5.</a> <strong>对于单文件电影，请直接上传单个的字幕文件，不要将同一部电影的不同语种字幕文件一同打包上传。</strong>如，在一个压缩包内同时囊括简中、繁中、中英，这是不允许的，你应将它们分别上传。<strong>对于迷你剧，则可以将每一集对应的字幕合并打包上传。</strong><i class=\"u-colorWarning\">Update! 2021-08-06</i>
                </li>
            </ul>",


    'upload_h40k_note' => "<ul>
        <li id='r4.0.1'><a href='#h4.0'><strong></strong></a> <a href='#r4.0.1'>4.0.1.</a> <strong>下面是在无特殊版本的情况下，一部影视作品的完整槽位（种子发布到站点后能占据的空余坑位，一旦占满，后来者就必须针对现有种子发起替代）表：</strong><i class=\"u-colorWarning\">Update! 2021-08-06</i>
        <table class='Table TableRuleSlot'>
            <tr class='Table-rowHeader'>
                <th class='Table-cell' style='width: 45px'>内容</th>
                <th class='Table-cell' style='width: 85px'>槽位类型</th>
                <th class='Table-cell' style='width: 160px'>字幕要求</th>
                <th class='Table-cell'>编码</th>
                <th class='Table-cell' style='width: 170px'>按清晰度划分</th>
                <th class='Table-cell'>说明</th>
            </tr>

            <tr class='Table-row'>
                <td class='Table-cell' rowspan='11'>影片主体</td>
                <td class='Table-cell' rowspan='2'>Encode<br/>中字质量槽</td>
                <td class='Table-cell' rowspan='2'>必须内封中字<br/>(硬字、外挂不算)</td>
                <td class='Table-cell'>x264</td>
                <td class='Table-cell'>SD / 720p / 1080p (3 槽)</td>
                <td class='Table-cell' rowspan='4'>替代优先级：视频质量佳 > 体积小</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell'>x265</td>
                <td class='Table-cell'>HDR 1080p / 2160p (2 槽)</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell' rowspan='2'>Encode<br/>英字质量槽</td>
                <td class='Table-cell' rowspan='2'>必须带英字<br/>(可封可挂，硬字不算)</td>
                <td class='Table-cell'>x264</td>
                <td class='Table-cell'>SD / 720p / 1080p (3 槽)</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell'>x265</td>
                <td class='Table-cell'>HDR 1080p / 2160p (2 槽)</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell' rowspan='2'>Encode<br/>存档槽</td>
                <td class='Table-cell' rowspan='2'>无要求</td>
                <td class='Table-cell'>x264</td>
                <td class='Table-cell'>720p / 1080p (2 槽)</td>
                <td class='Table-cell' rowspan='2'>替代优先级：同体积更高质量优先，同质量更小体积优先</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell'>x265</td>
                <td class='Table-cell'>2160p (1 槽)</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell' rowspan='2'>Encode<br/>特色槽</td>
                <td class='Table-cell' rowspan='2'>必须内封中字或带国配<br/>(硬字、外挂不算)</td>
                <td class='Table-cell'>x264</td>
                <td class='Table-cell'>720p / 1080p (2 槽)</td>
                <td class='Table-cell' rowspan='2'>替代优先级：国配／特效字幕丰富 > 国配／特效字幕质量佳 > 视频质量优<br/>仅限外语片，单独包含特效或国配无法替代另一种</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell'>x265</td>
                <td class='Table-cell'>2160p (1 槽)</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell'>Remux 槽</td>
                <td class='Table-cell'>无要求</td>
                <td class='Table-cell'>-</td>
                <td class='Table-cell'>720p / 1080p / 2160p (3 槽)</td>
                <td class='Table-cell'>替代优先级：原盘质量优 > 带内封中</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell'>原盘槽</td>
                <td class='Table-cell'>无要求</td>
                <td class='Table-cell'>-</td>
                <td class='Table-cell'>SD×2 / 720p / 1080p / 2160p (5 槽)</td>
                <td class='Table-cell'>替代优先级：原盘质量优<br/>蓝光原盘可以 ISO 镜像或文件夹的形式发布</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell'>DIY 原盘槽</td>
                <td class='Table-cell'>无要求</td>
                <td class='Table-cell'>-</td>
                <td class='Table-cell'>1080p / 2160p (2 槽)</td>
                <td class='Table-cell'>替代优先级：国配／特效字幕更丰富 > 国配／特效字幕质量更佳 > 视频质量更优<br/>仅限外语片，单独包含特效或国配无法替代另一种，可以 ISO 镜像和文件夹的形式发布</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell' rowspan='2'>附加内容</td>
                <td class='Table-cell' rowspan='2'>Encode<br/>质量槽</td>
                <td class='Table-cell' rowspan='2'>无要求</td>
                <td class='Table-cell'>x264</td>
                <td class='Table-cell'>SD / 720p / 1080p (3 槽)</td>
                <td class='Table-cell' rowspan='2'>替代优先级：视频质量佳 > 体积小</td>
            </tr>
            <tr class='Table-row'>
                <td class='Table-cell'>x265</td>
                <td class='Table-cell'>2160p (1 槽)</td>
            </tr>
        </table>
        <li id='r4.0.2'><a href='#h4.0'><strong></strong></a> <a href='#r4.0.2'>4.0.2.</a> <strong>槽位类型：</strong>指示了该槽位所容纳资源的类型并方便称呼和记忆，依据种子的处理、字幕、音轨情况划分。
            <ul>
                <li id='r4.0.2.1'><a href='#r4.0.2'><strong></strong></a> <a href='#r4.0.2.1'>4.0.2.1.</a> 进入 <strong>“中字质量槽”</strong> 的种子<strong>必须</strong>内封中字（简繁不限）。可以进入中字槽的字幕组合有：中、中+英、中+原（指原始语言字幕，如日语字幕），这三大类字幕组合的种子只进入中字质量槽。其他所有字幕组合，均进入英字质量槽。
                </li>
                <li id='r4.0.2.2'><a href='#r4.0.2'><strong></strong></a> <a href='#r4.0.2.2'>4.0.2.2.</a> 进入 <strong>“中字质量槽”</strong> 的外语片，除原始语言音轨以外，含非国配音轨会被视为冗余，含国配音轨则进入<strong>特色槽</strong>。
                </li>
                <li id='r4.0.2.3'><a href='#r4.0.2'><strong></strong></a> <a href='#r4.0.2.3'>4.0.2.3.</a> 进入 <strong>“英字质量槽”</strong> 的种子，非英语电影<strong>必须</strong>内封或外挂英字，并推荐将英字设为默认字幕。通常，种子组的首个无字幕种子会进入该槽位，如果其是非英语电影则会被标记为 “可替代”，更多请看 <a href='#r5.4.14'>5.4.14</a> 。<i class=\"u-colorWarning\">Update! 2021-08-06</i>
                </li>
            </ul>
        </li>
        <li id='r4.0.3'><a href='#h4.0'><strong></strong></a> <a href='#r4.0.3'>4.0.3.</a> <strong>字幕要求：</strong>囊括了中文的多语字幕（如中英双语字幕等），一律按中文字幕标记和处理。
        </li>
        <li id='r4.0.4'><a href='#h4.0'><strong></strong></a> <a href='#r4.0.4'>4.0.4.</a> <strong>槽位类型：</strong>指示了该槽位所容纳资源的类型并方便称呼和记忆。<strong>说明：</strong>阐明该槽位发起重复和替代的依据，以及替代因素的优先级排序。
            <ul>
                <li id='r4.0.4.1'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.1'>4.0.4.1.</a> <strong>质量槽：</strong>该槽位仅以压制质量为考虑因素，压制应在保证压制后画面与源基本无可感知差异的前提下尽可能减小码率，音轨应满足 <a href='#r5.4.3'>5.4.3</a> 的指导要求，不允许包含冗余的配音音轨。
                </li>
                <li id='r4.0.4.2'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.2'>4.0.4.2.</a> <strong>存档槽：</strong>该槽位以体积和压制质量为考虑因素，压制应在保证质量过关的前提下尽可能减小码率，该槽位基本以 0day/Scene 压制作品为参照。不允许包含冗余的配音音轨。
                </li>
                <li id='r4.0.4.3'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.3'>4.0.4.3.</a> <strong>特色槽：仅外语片适用。</strong>该槽位以内容丰富程度为主要考虑因素，压制应在保证质量过关的前提下尽可能多地加入特效字幕、国配音轨等特色内容。发布者必须在种子描述中针对特色的具体情况作出说明，比如，增加了某某国配，增加了怎样的特效字幕并配以截图。
                    <ul>
                        <li id='r4.0.4.3.1'><a href='#r4.0.4.3'><strong></strong></a> <a href='#r4.0.4.3.1'>4.0.4.3.1.</a> <strong>国配：</strong>仅外语电影（不包括粤语等方言电影）可添加此标记。仅普通话（含台湾普通话）被视为国配，粤语等地方方言不被视为国配。
                        </li>
                        <li id='r4.0.3.3.2'><a href='#r4.0.3.3'><strong></strong></a> <a href='#r4.0.3.3.2'>4.0.3.3.2.</a> <strong>特效字幕：</strong>带有反光、闪烁、移动、翻滚、漂移、颜色、二维、三维、分裂、组合等特殊效果的字幕，应用特效的目的是与电影场景尽可能匹配、和谐。简单的变色、字体处理不被视为特效，如果你不确定自己种子的字幕是否属于特效字幕，可以在论坛 <a href='forums.php?action=viewforum&forumid=31'>求助区</a> 询问。在发布时，你必须提供能展现特效字幕绚丽性的截图。特效截图无分辨率和格式要求，至少两张，<strong>必须截取影片剧情相关部分的特效</strong>（不要截取片头片尾的人员名单或片商名称特效等等没有意义的部分），不要和影片截图混放，且不计入 <a href='#h2.2'>三张截图的基本要求</a>，也就是共计至少五张。<i class=\"u-colorWarning\">Update! 2021-08-06</i>
                        </li>
                    </ul>
                </li>
                <li id='r4.0.4.4'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.4'>4.0.4.4.</a> <strong>Remux 槽：</strong>该槽位的首要考虑因素是 Remux 源音视频轨的质量。与特色槽、DIY 槽不同，该槽位不考虑特效中字、国配音轨。
                </li>
                <li id='r4.0.4.5'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.5'>4.0.4.5.</a> <strong>原盘槽：</strong>该槽位的首要考虑因素是原盘音视频轨的质量。
                </li>
                <li id='r4.0.4.6'><a href='#r4.0.4'><strong></strong></a> <a href='#r4.0.4.6'>4.0.4.6.</a> <strong>DIY 原盘槽：仅外语片适用。</strong>该槽位的首要考虑因素是原盘音视频轨的质量。在质量相同的前提下，应尽可能多地加入特效字幕、国配音轨等特色内容。发布者必须在种子描述中针对加入的特色内容作出说明，比如，增加了某某国配，增加了怎样的特效字幕并配以截图。请注意，<strong>中文影片（包括粤语闽南语等方言影片）不享受此规则与槽位</strong>，不允许上传华语影片的 DIY 原盘。如果你认为手中的华语影片 DIY 原盘具有<strong>特别的价值</strong>，请移步 <a href='forums.php?action=viewthread&threadid=21'>我能发布它吗</a>。<i class=\"u-colorWarning\">Update! 2021-08-06</i>
                </li>
            </ul>
        </li>
        <li id='r4.0.5'><a href='#h4.0'><strong></strong></a> <a href='#r4.0.5'>4.0.5.</a> <strong>对所有槽位：</strong>方言（如粤语）电影不能使用 “国语配音” “特效字幕” 标记，其中的普通话配音音轨也不被视为冗余。
        </li>
        </ul>",
    'upload_h41k_note' => "
            <ul>
                <li id='r4.1.1'><a href='#h4.1'><strong></strong></a> <a href='#r4.1.1'>4.1.1.</a> <strong>对于给定电影，提供 1 个英字质量槽，1 个中字质量槽，共计 2 个 x264 Encode 槽位。</strong><i class=\"u-colorWarning\">New! 2021-08-06</i>
                </li>
                <li id='r4.1.2'><a href='#h4.1'><strong></strong></a> <a href='#r4.1.2'>4.1.2.</a> 更多关于标清发布的信息，参见 <a href='#h3.1'>相关规则</a>。
                </li>
            </ul>",

    'upload_h42k_note' => "
            <ul>
                <li id='r4.2.1'><a href='#h4.2'><strong></strong></a> <a href='#r4.2.1'>4.2.1.</a> <strong>对于给定电影，4 个不同的 x264 720p Encode 和 4 个不同的 x264 1080p Encode 可以共存。</strong>
                <ul>
                        <li id='r4.2.1.1'><a href='#r4.2.1'><strong></strong></a> <a href='#r4.2.1.1'>4.2.1.1.</a> <strong>每组包含一个中字质量槽，一个英字质量槽，一个存档槽和一个特色槽。</strong>存档槽的编码应趋向压缩程度更高、更紧凑，而质量槽应趋向尽可能高的质量。作为参考，存档槽的 Encode 应至少比质量槽小约 20% 才能共存。
                        </li>
                    </ul>
                </li>
                <li id='r4.2.2'><a href='#h4.2'><strong></strong></a> <a href='#r4.2.2'>4.2.2.</a> <strong>另外，还有 2 个额外的槽位留给 HDR x265 1080p Encode。</strong>它与规则 <a href='#r4.2.1'>4.2.1</a> 所定义的槽位相独立，且互不干涉。该组槽位分别留给中字槽和英字槽的尽可能高质量的编码。<strong>不允许发布 SDR x265 1080p Encode。</strong>例外：动漫类影片可以发布 SDR 10-bit x265 1080p Encode，<strong>但不允许 SDR 10-bit x264 Encode，</strong>因为它们的设备兼容性较差。
                </li>
                <li id='r4.2.3'><a href='#h4.2'><strong></strong></a> <a href='#r4.2.3'>4.2.3.</a> 更多关于高清发布的信息，参见 <a href='#h3.2'>相关规则</a>。
                </li>
            </ul>",

    'upload_h43k_note' => "
            <ul>
                <li id='r4.3.1'><a href='#h4.3'><strong></strong></a> <a href='#r4.3.1'>4.3.1.</a> <strong>对于给定电影，有 4 个 2160p Encode 可以共存。</strong>
                <ul>
                        <li id='r4.3.1.1'><a href='#r4.3.1'><strong></strong></a> <a href='#r4.3.1.1'>4.3.1.1.</a> <strong>每组包含一个中字质量槽，一个英字质量槽，一个存档槽和一个特色槽。</strong>存档槽的编码应趋向压缩程度更高、更紧凑，而质量槽应趋向尽可能高的质量。作为参考，存档槽的 Encode 应至少比质量槽小约 20% 才能共存。
                        </li>
                        <li id='r4.3.1.2'><a href='#r4.3.1'><strong></strong></a> <a href='#r4.3.1.2'>4.3.1.2.</a> <strong>如果提供了足够多的对比截图证明其优于既存的高清源，则一个 SDR 种子可占据规则 <a href='#r4.3.1.1'>4.3.1.1</a> 定义的存档槽。</strong>
                        </li>
                    </ul>
                </li>
                <li id='r4.3.2'><a href='#h4.3'><strong></strong></a> <a href='#r4.3.2'>4.3.2.</a> 更多关于超高清发布的信息，参见 <a href='#h3.3'>相关规则</a>。
                </li>
            </ul>",

    'upload_h44k_note' => "
            <ul>
                <li id='r4.4.1'><a href='#h4.4'><strong></strong></a> <a href='#r4.4.1'>4.4.1.</a> <strong>允许存在一个 NTSC DVD 原盘种子和一个 PAL DVD 原盘种子。两个槽位都应以能获取到的最优质源占据，由管理决定。</strong>
                </li>
                <li id='r4.4.2'><a href='#h4.4'><strong></strong></a> <a href='#r4.4.2'>4.4.2.</a> <strong>720p 下，允许存在一个原盘种子和一个 Remux 种子。</strong>两个槽位都应以能获取到的最优质源占据，原盘第二考虑国配，Remux 第二考虑内封中字，由管理决定。
                </li>
                <li id='r4.4.3'><a href='#h4.4'><strong></strong></a> <a href='#r4.4.3'>4.4.3.</a> <strong>1080p/2160p 下，各允许存在一个原盘种子，一个 DIY 原盘种子和一个 Remux 种子。</strong>三个槽位都应以能获取到的最优质源占据，由管理决定。
                </li>
                <li id='r4.4.4'><a href='#h4.4'><strong></strong></a> <a href='#r4.4.4'>4.4.4.</a> 更多关于原盘发布的信息，参见 <a href='#h3.4'>相关规则</a>。
                </li>
            </ul>",

    'upload_h45k_note' => "
            <ul>
                <li id='r4.5.1'><a href='#h4.5'><strong></strong></a> <a href='#r4.5.1'>4.5.1.</a> <strong>每种分辨率（SD、720p、1080p 还额外允许 Remux）允许存在一个含附加内容的合集。</strong>
                </li>
                <li id='r4.5.2'><a href='#h4.5'><strong></strong></a> <a href='#r4.5.2'>4.5.2.</a> <strong>假设内容存在实际区别，则来自不同版本的附加内容合集可以共存。</strong>若无区别，该槽位应留给最完整的合集。
                </li>
            </ul>",
    'upload_h46k_note' => "
            <ul>
                <li id='r4.6.1'><a href='#h4.6'><strong></strong></a> <a href='#r4.6.1'>4.6.1.</a> <strong>允许电影的每种剪辑版本（影院／导演、限制级／未分级……）拥有一组独立的槽位。另外，各类 HDR 格式各自拥有一组独立的槽位。</strong><i class=\"u-colorWarning\">Update! 2021-08-06</i>
                </li>
                <li id='r4.6.2'><a href='#h4.6'><strong></strong></a> <a href='#r4.6.2'>4.6.2.</a> <strong>外语电影（不包括粤语等方言电影）的国语配音种子（同时包含原声和配音音轨更好）进入特色槽。</strong>
                </li>
				<li id='r4.6.3'><a href='#h4.6'><strong></strong></a> <a href='#r4.6.3'>4.6.3.</a> <strong>非英语电影的英配种子（双音轨更好）可拥有一个额外的英字质量槽。</strong>即使英配电影的内封字幕情况符合中字质量槽标准，它也优先进入英字质量槽。 <strong>方言影片如果不包含国配或英配则可拥有一个额外的中字质量槽。</strong>即使内封字幕情况符合英字槽条件，它也优先进入中字质量槽。<i class=\"u-colorWarning\">Update! 2021-08-14</i>
                </li>
                <li id='r4.6.4'><a href='#h4.6'><strong></strong></a> <a href='#r4.6.4'>4.6.4.</a> <strong>虽然对于给定电影，每个种子都应源自被视为最佳的版本，但可以额外提供给源自稍差但提供了不同观赏体验版本的种子一组槽位。</strong>该槽位组通常由每个分辨率的一个 Encode、一个 Remux 和一个完整的原盘组成（这里没有存档槽）。如果你不确定能否共存，请移步 <a href='forums.php?action=viewthread&threadid=21'>我能发布它吗</a>。<i class=\"u-colorWarning\">Update! 2021-08-14</i>
                </li>
            </ul>",

    'upload_h51k_note' => "
            <ul>
                <li id='r5.1.1'><a href='#h5.1'><strong></strong></a> <a href='#r5.1.1'>5.1.1.</a> <strong>对于标清种子通常的替代顺序如下：VHS < TV < HDTV | WEB < DVD < Blu-ray。对于高清和超高清种子通常的替代顺序如下：HDTV < WEB | HD-DVD | Blu-ray。</strong>如果码率高低悬殊，蓝光、HD-DVD 源的种子可直接替代 TV、HDTV 源的同槽位种子，无需提供用于佐证的对比图。
                    <ul>
                        <li id='r5.1.1.1'><a href='#r5.1.1'><strong></strong></a> <a href='#r5.1.1.1'>5.1.1.1.</a> <strong>虽说这个替代顺序一般都没问题，但请注意决定的作出最终要落实在质量上（比如，如果蓝光源被发现是次品，则 WEB Encode 就不会被删除）。</strong>
                        </li>
                    </ul>
                </li>
                <li id='r5.1.2'><a href='#h5.1'><strong></strong></a> <a href='#r5.1.2'>5.1.2.</a> <strong>未经删减的原盘种子总是能替代相同质量的、抛弃部分内容（比如附加内容或菜单）的种子。</strong>
                </li>
            </ul>",

    'upload_h52k_note' => "
            <ul>
                <li id='r5.2.1'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.1'>5.2.1.</a> <strong>任何未满足 <a href='#h3'>相关规则</a> 定义的首选格式的种子都可被符合推荐格式的种子替代，只要其质量同等或更优。</strong>
                    <ul>
                        <li id='r5.2.1.1'><a href='#r5.2.1'><strong></strong></a> <a href='#r5.2.1.1'>5.2.1.1.</a> <strong>x264 (SD, HD) 和 x265 (UHD) 被视为首选编码器。</strong>来源信息未知的 H.264 或 H.265 文件可以占据规则 <a href='#r4.1.1'>4.1.1</a>、<a href='#r4.2.1'>4.2.1</a> 和 <a href='#r4.3.1'>4.3.1</a> 定义的 x264 或 x265 槽位，但更容易因质量原因被替代。
                        </li>
						<li id='r5.2.1.2'><a href='#r5.2.1'><strong></strong></a> <a href='#r5.2.1.2'>5.2.1.2.</a> <strong>杜比视界 Remux 和国内流媒体的 WEB-DL，这两者可以 MP4 容器的形式存在，不会被其他种子以 “使用 MKV 容器” 为由替代。</strong><i class=\"u-colorWarning\">New! 2021-08-14</i>
                        </li>
                    </ul>
                </li>
                <li id='r5.2.2'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.2'>5.2.2.</a> <strong>占据了规则 <a href='#r4.1.1.1'>4.1.1.1</a>、<a href='#r4.2.1.1'>4.2.1.1</a> 和 <a href='#r4.3.1.1'>4.3.1.1</a> 定义的高质量槽位的种子可被质量显著更佳的 Encode 替代。</strong>
                </li>
                <li id='r5.2.3'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.3'>5.2.3.</a> <strong>可替代种子可以被无标记所指问题的种子替代。</strong>见规则 <a href='#h5.4'>5.4</a> 了解完整的可替代标记清单。
                </li>
                <li id='r5.2.4'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.4'>5.2.4.</a> <strong>源（原盘、Remux）类种子的替代政策较激进，即提供更好观看体验的来源将胜过较低劣的。</strong>
                </li>
                <li id='r5.2.5'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.5'>5.2.5.</a> <strong>质量替代（对于 Encode 和源）应尽可能多地通过截图对比来证明改进。</strong>
                </li>
                <li id='r5.2.6'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.6'>5.2.6.</a> <strong>如果出现重大缺陷（不完整、音轨不同步、错误的纵横比……），则劣质 Scene 出品可自动被 REPACK 或 PROPER 替代掉。不是因影响观影体验的问题（被盗的源、重复、命名错误）， 劣质的 Scene 出品可以此方式替代。</strong>
                </li>
                <li id='r5.2.7'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.7'>5.2.7.</a> <strong>Remux 可被同等质量但更为完整的种子替代，下列是可能的替代原因：</strong>
                    <ul>
                        <li id='r5.2.7.1'><a href='#r5.2.7'><strong></strong></a> <a href='#r5.2.7.1'>5.2.7.1.</a> 包含了旧种所不包含的章节信息。
                        </li>
                        <li id='r5.2.7.2'><a href='#r5.2.7'><strong></strong></a> <a href='#r5.2.7.2'>5.2.7.2.</a> 添加了评论或独立的配乐。
                        </li>
                        <li id='r5.2.7.3'><a href='#r5.2.7'><strong></strong></a> <a href='#r5.2.7.3'>5.2.7.3.</a> 以适当的相同内容的无损压缩音轨替换旧 Remux 的 PCM 音轨。
                        </li>
                        <li id='r5.2.7.4'><a href='#r5.2.7'><strong></strong></a> <a href='#r5.2.7.4'>5.2.7.4.</a> 包含了旧种所不包含的中文 PGS/SUP 字幕。
                        </li>
                        <li id='r5.2.7.5'><a href='#r5.2.7'><strong></strong></a> <a href='#r5.2.7.5'>5.2.7.5.</a> 在管理批准的情况下，影片后期制作存在显著差异的种子允许共存。
                        </li>
                    </ul>
                </li>
                <li id='r5.2.8'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.8'>5.2.8.</a> <strong>对于各类槽位的具体替代序列如下：</strong>
                    <ul>
                        <li id='r5.2.8.1'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.1'>5.2.8.1.</a> <strong>质量槽：</strong>由于已经拆成了中字、英字两个槽位，因此，压制质量是唯一替代考虑因素，欲替代旧种的发布者需要提交尽可能多的对比截图来证明改进。此外，还存在对字幕无硬性要求的质量槽（适用于附加内容）。
						</li>
                        <li id='r5.2.8.2'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.2'>5.2.8.2.</a> <strong>存档槽：</strong>在保证质量的前提下，无内封中字 < 内封中字。
                        </li>
                        <li id='r5.2.8.3'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.3'>5.2.8.3.</a> <strong>特色槽：</strong>在保证质量的前提下，内封中字/国配有其一 < 内封中字+国配 < 内封特效中字+国配。
                            <ul>
                                <li id='r5.2.8.3.1'><a href='#r5.2.8.3'><strong></strong></a> <a href='#r5.2.8.3.1'>5.2.8.3.1.</a> <strong>国配：</strong>无国配 < 台湾配音 < 大陆配音。
                                </li>
                                <li id='r5.2.8.3.2'><a href='#r5.2.8.3'><strong></strong></a> <a href='#r5.2.8.3.2'>5.2.8.3.2.</a> <strong>保证质量：</strong>视频轨码率应高于 Scene，且欲替代旧种的种子，其视频轨码率应超出 Scene 15%。
                                </li>
                            </ul>
                        </li>
                        <li id='r5.2.8.4'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.4'>5.2.8.4.</a> <strong>Remux 槽：</strong>无中字普通源 < 内封中字普通源 < 无中字优质源 < 内封中字优质源。有无国配不纳入替代考虑因素。
                        </li>
                        <li id='r5.2.8.5'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.5'>5.2.8.5.</a> <strong>原盘槽：</strong>无中字普通源 < 内封中字普通源 < 无中字优质源 < 内封中字优质源。有无国配不纳入替代考虑因素。
                        </li>
                        <li id='r5.2.8.6'><a href='#r5.2.8'><strong></strong></a> <a href='#r5.2.8.6'>5.2.8.6.</a> <strong>DIY 原盘槽：</strong>无内封中字 < 内封中字 < 内封特效中字 < 内封中字+国配 < 内封特效中字+国配。
                        </li>
                    </ul>
                </li>
                <li id='r5.2.9'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.9'>5.2.9.</a> <strong>非中文电影的评论音轨替代序列：</strong>
                    <ul>
                        <li id='r5.2.9.1'><a href='#r5.2.9'><strong></strong></a> <a href='#r5.2.9.1'>5.2.9.1.</a> <strong>Remux 槽：</strong>同源、同含主音轨中字的情况下，无评论音轨 < 有评论音轨；有评论音轨时，无评论音轨字幕 < 英文评论音轨字幕 < 中文评论音轨字幕。
                        </li>
                        <li id='r5.2.9.2'><a href='#r5.2.9'><strong></strong></a> <a href='#r5.2.9.2'>5.2.9.2.</a> <strong>DIY 原盘槽：</strong>同源、其他内容与既有种子相近的情况下，无评论音轨 < 有评论音轨；有评论音轨时，无评论音轨字幕 < 英文评论音轨字幕 < 中文评论音轨字幕。
                        </li>
                    </ul>
                </li>
                <li id='r5.2.10'><a href='#h5.2'><strong></strong></a> <a href='#r5.2.10'>5.2.10.</a> <strong>脏线、色带、色块等原盘瑕疵的修复不可单独作为替代理由。</strong>
                </li>
            </ul>",

    'upload_h53k_note' => "
            <ul>
                <li id='r5.3.1'><a href='#h5.3'><strong></strong></a> <a href='#r5.3.1'>5.3.1.</a> <strong>任何不活跃超过 4 周将自动可替代。</strong>
                </li>
                <li id='r5.3.2'><a href='#h5.3'><strong></strong></a> <a href='#r5.3.2'>5.3.2.</a> <strong>任何发布 24 小时后仍未做种的种子会被标记为 “可替代”。</strong>
                </li>
                <li id='r5.3.3'><a href='#h5.3'><strong></strong></a> <a href='#r5.3.3'>5.3.3.</a> <strong>如有可能，尽量为不活跃种子（死种）续种而不是替代之。</strong>
                </li>
            </ul>",

    'upload_h54k_note' => "<ul>
                <li id='r5.4.1'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.1'>5.4.1.</a> <strong>问题纵横比：</strong>编码错误是导致种子表现出错误纵横比的原因。
                </li>
                <li id='r5.4.2'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.2'>5.4.2.</a> <strong>非原始纵横比：</strong>该种子的纵横比与原始的、影院上映的电影不同，一旦存在纵横比正确的发行，同分辨率组内就不允许共存非原始纵横比的种子了。
                </li>
                <li id='r5.4.3'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.3'>5.4.3.</a> <strong>无谓高码（臃肿）：</strong>种子的视频或音频比特率过高。音频比特率上限手册：<i class=\"u-colorWarning\">Update! 2021-08-06</i>
                <table class='Table TableRuleAudio'>
                <tr class='Table-rowHeader'>
                    <td class='Table-cell' colspan='2' rowspan='2'>源音频</td>
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
                    <td class='Table-cell' rowspan='3'>主音轨</td>
                    <td class='Table-cell'>7.1/5.1 无损</td>
                    <td class='Table-cell'>640 kbps AC3 (首选 448 kbps AC3)</td>
                    <td class='Table-cell'>1509 kbps DTS (首选 640  kbps AC3)</td>
                    <td class='Table-cell'>1536 kbps E-AC3</td>
                    <td class='Table-cell'>保持原样</td>
                    <td class='Table-cell'>保持原样</td>
                    <td class='Table-cell'>保持原样</td>
                </tr>
                <tr class='Table-row'>
                    <td class='Table-cell'>2.0/1.0 无损</td>
                    <td class='Table-cell'>16-bit FLAC (首选高质量 AAC)</td>
                    <td class='Table-cell'>16-bit FLAC (首选高质量 AAC)</td>
                    <td class='Table-cell'>16-bit FLAC</td>
                    <td class='Table-cell'>保持原样 (首选 24-bit FLAC)</td>
                    <td class='Table-cell'>保持原样 (首选 FLAC)</td>
                    <td class='Table-cell'>保持原样 (首选 FLAC)</td>
                </tr>
                <tr class='Table-row'>
                    <td class='Table-cell'>有损</td>
                    <td class='Table-cell'>保持原样</td>
                    <td class='Table-cell'>保持原样</td>
                    <td class='Table-cell'>保持原样</td>
                    <td class='Table-cell'>保持原样</td>
                    <td class='Table-cell'>保持原样</td>
                    <td class='Table-cell'>保持原样</td>
                </tr>
                <tr class='Table-row'>
                    <td class='Table-cell' rowspan='2'>次音轨</td>
                    <td class='Table-cell'>无损</td>
                    <td class='Table-cell'>中质量 AAC</td>
                    <td class='Table-cell'>高质量 AAC</td>
                    <td class='Table-cell'>16-bit FLAC</td>
                    <td class='Table-cell'>16-bit FLAC</td>
                    <td class='Table-cell'>保持原样 (首选 FLAC)</td>
                    <td class='Table-cell'>保持原样 (首选 FLAC)</td>
                </tr>
                <tr class='Table-row'>
                    <td class='Table-cell'>有损</td>
                    <td class='Table-cell'>中质量 AAC 或保持原样</td>
                    <td class='Table-cell'>中质量 AAC 或保持原样</td>
                    <td class='Table-cell'>中质量 AAC 或保持原样</td>
                    <td class='Table-cell'>中质量 AAC 或保持原样</td>
                    <td class='Table-cell'>保持原样</td>
                    <td class='Table-cell'>保持原样</td>
                </tr>
            </table>
                </li>
                <li id='r5.4.4'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.4'>5.4.4.</a> <strong>音轨冗余：不适用于原盘槽和 DIY 原盘槽。</strong>种子包含多余的音轨，有如非中英配音或同一音轨的冗余版本。<a href='#r4.0.4'>4.0.4.</a> 方言（如粤语）电影中的普通话配音音轨不被视为冗余。
                </li>
                <li id='r5.4.5'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.5'>5.4.5.</a> <strong>反交错问题：</strong>种子被错误地反交错了。
                </li>
                <li id='r5.4.6'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.6'>5.4.6.</a> <strong>帧率错误：</strong>种子以不同于原生、正确的帧率播放。
                </li>
                <li id='r5.4.7'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.7'>5.4.7.</a> <strong>字幕不同步：</strong>种子中包含的字幕有效，但不同步。
                </li>
                <li id='r5.4.8'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.8'>5.4.8.</a> <strong>格式不当：</strong>种子不符合我们的 <a href='#h3'>推荐格式</a>。
                </li>
                <li id='r5.4.9'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.9'>5.4.9.</a> <strong>分辨率不当：</strong>种子不符合我们的 <a href='#h3'>推荐分辨率</a>。
                </li>
                <li id='r5.4.10'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.10'>5.4.10.</a> <strong>劣质源：</strong>种子的源没能提供当下能获取到的最好观影体验。
                </li>
                <li id='r5.4.11'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.11'>5.4.11.</a> <strong>低质量：</strong>种子编码所使用的源非常糟糕，或是受到了重大质量问题的影响。
                </li>
                <li id='r5.4.12'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.12'>5.4.12.</a> <strong>播放问题：</strong>通常会由次级标记详细说明导致种子无法完美播放或编码的问题。
                </li>
                <li id='r5.4.13'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.13'>5.4.13.</a> <strong>残缺：</strong>种子缺失内容，通常会由次级标记详细说明。
                </li>
                <li id='r5.4.14'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.14'>5.4.14.</a> <strong>缺少基本字幕：适用于英字质量槽、存档槽、Remux 槽和原盘槽</strong>，同时缺少中英字幕（内封或外挂均无）的非英文影片会被标记，标记可通过外挂要求的字幕来消除。默片或无需字幕的影片不会被添加此标记。
                    <ul>
                        <li id='r5.4.14.1'><a href='#r5.4.14'><strong></strong></a> <a href='#r5.4.14.1'>5.4.14.1.</a> <strong>英字质量槽：</strong>外挂英语字幕可消除标记。
                        </li>
                        <li id='r5.4.14.2'><a href='#r5.4.14'><strong></strong></a> <a href='#r5.4.14.2'>5.4.14.2.</a> <strong>其他三类：</strong>外挂中／英字幕可消除标记。可被内封中字的同等或更优质量种子所替代。
                        </li>
                    </ul>
                </li>
                <li id='r5.4.15'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.15'>5.4.15.</a> <strong>未强制英文字幕：仅适用于英字槽，</strong>种子的重要非英语对白不包含单独的英文字幕。
                </li>
                <li id='r5.4.16'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.16'>5.4.16.</a> <strong>无原声音轨：</strong>一部影片在同时没有原声音轨、国语配音和英语配音的情况下（仅包含小语种配音）适用此标记。<i class=\"u-colorWarning\">Update! 2021-08-14</i>
                </li>
                <li id='r5.4.17'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.17'>5.4.17.</a> <strong>音轨不同步：</strong>种子中包含的音轨有效，但不同步。
                </li>
                <li id='r5.4.18'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.18'>5.4.18.</a> <strong>问题裁边：</strong>种子明显裁边过多或过少。
                </li>
                <li id='r5.4.19'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.19'>5.4.19.</a> <strong>劣质字幕翻译：</strong>种子中包含的字幕质量很差，且不是电影的准确翻译。
                </li>
                <li id='r5.4.20'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.20'>5.4.20.</a> <strong>硬字幕：</strong>种子中的字幕被硬编码在视频轨中。此标记不针对硬编码强制字幕。
                </li>
                <li id='r5.4.21'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.21'>5.4.21.</a> <strong>劣质转码：</strong>种子的音频轨编码自已有损压缩的源。
                </li>
                <li id='r5.4.22'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.22'>5.4.22.</a> <strong>含水印：</strong>种子含有明显的水印。
                </li>
                <li id='r5.4.23'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.23'>5.4.23.</a> <strong>放大：</strong>种子编码自低分辨率源。
                </li>
                <li id='r5.4.24'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.24'>5.4.24.</a> <strong>不活跃：</strong>种子无人做种已达至少 4 周。这个标记是自动添加和去除的。
                </li>
                <li id='r5.4.25'><a href='#h5.4'><strong></strong></a> <a href='#r5.4.25'>5.4.25.</a> <strong>冗余文件：</strong>种子包含无关的文件，详见 <a href='#r2.1.3'>2.1.3</a> 。<i class=\"u-colorWarning\">New! 2021-12-31</i>
            </ul>",









    'chat_title' => "社交",
    'chat_general' => "社交总则",
    'chat_general_rules' => "<ul>
    <li>无视管理员劝阻继续争吵会造成严重的后果。</li>
    <li>禁止骚扰管理员或其他用户。不要对他人的私事妄加评论。</li>
    <li>禁止发广告、刷屏、辱骂、歧视等。</li>
    <li>禁止讨论恐怖主义、人种、宗教、政治、性取向、族裔背景等敏感话题。</li>
    <li>管理组对社交规则拥有进一步补充和解释的权利，对于冲突有最终裁决权。</li>
    </ul>",
    'chat_groups' => "群聊规则",
    'chat_forum' => "论坛规则不允许的行为在交流群也不允许，反之亦然。分开来写仅仅是为了方便。",
    'chat_forums' => "论坛规则",
    'chat_forums_rules' => "<li>发帖前先阅读对应版块的版规。</li>
        <li>不要发布与版块主题无关的帖子。发布在非邀请区的发邀求邀贴会带来严重的后果。</li>
        <li>禁止通过非交易区帖子获取经济利益。</li>
        <li>不要不分场合地大肆宣传你发布的资源。</li>
        <li>不要过度引用。在引用他人的发言时，请尽量只引用必要的一小部分，尤其是避免引用图片。</li>
        <li>不要发布不合适的成人内容。发布涉及超出容忍范围的性与暴力内容的帖子会导致你被警告或带来更严重的后果。帖子中的成人内容必须正确标记，正确的格式如下：[mature=描述] ……内容…… [/mature]，其中 “描述” 是对帖子内容的强制性描述。错误或是不充分的描述会导致惩罚。专门为发布成人内容所创建的主题会被删除。成人内容（包括写真封面）应与你在论坛中发布的帖子在内容上相关。如果你对帖子是否合适不太确定，请向论坛管理员 <a href='/staff.php'>发送私信</a> 并在进一步操作之前等待回复。</li>",
    'chat_forums_irc' => "<li>禁止在本站管辖的任何场合贬低、诋毁任何 PT 站。</li>
        <li>所有人都是从萌新之路开始的，如果可以，请尽量耐心，帮助新人。</li>",
    'tags_title' => "标签",
    'tags_summary' => "
        <li>标签应以英文逗号（ “,” ）分隔，你应使用英文点号（ “.” ）来分隔标签内的单词——例如 “<strong class='u-colorSuccess'>sci.fi</strong>”。</li>

        <li>请使用 <a href='upload.php' target='_blank'>左侧文本框的官方标签</a>，而不是 “非官方” 标签（例如使用官方的 “<strong class='u-colorSuccess'>drama</strong>” 标签，而不是非官方的  “<strong class='u-colorWarning'>holy.crap</strong>” 标签）。<strong>请注意 “<strong class='u-colorSuccess'>2000s</strong>” 表示 2000 到 2009 之间。</strong></li>

        <li>不要添加 “无用” 的标签，如 “<strong class='u-colorWarning'>seen.live</strong>” 、 “<strong class='u-colorWarning'>awesome</strong>” 、 “<strong class='u-colorWarning'>kung.fu</strong>” （包含在 “<strong class='u-colorSuccess'>action</strong>” ）等。如果是现场表演，你可以添加 “<strong class='u-colorSuccess'>live</strong>”。</li>

        <li>仅添加电影本身的信息，而不是某个具体版本的信息</strong>。严禁使用 “<strong class='u-colorWarning'>remux</strong>” 、 “<strong class='u-colorWarning'>encode</strong>” 、 “<strong class='u-colorWarning'>blu.ray</strong>” 、 “<strong class='u-colorWarning'>eac3to</strong>” 等。请记住，他们仅用以标明同一电影的其他版本，本身并非标签。</li>

        <li><strong>如果你对 <a href='upload.php'>左侧文本框的官方标签</a> 有疑问，那就不要添加进去。</strong></li>",
    'tags_summary_onupload' => "
    <li>标签应以英文逗号（ “,” ）分隔，你应使用英文点号（ “.” ）来分隔标签内的单词——例如 “<strong class='u-colorSuccess'>sci.fi</strong>”。</li>

    <li>请使用<a href='upload.php'>左侧文本框的官方标签</a>，而不是 “非官方” 标签（例如使用官方的 “<strong class='u-colorSuccess'>drama</strong>” 标签，而不是非官方的  “<strong class='u-colorWarning'>holy.crap</strong>” 标签）。<strong>请注意 “<strong class='u-colorSuccess'>2000s</strong>” 表示 2000 到 2009 之间。</strong></li>

    <li>不要添加 “无用” 的标签，如 “<strong class='u-colorWarning'>seen.live</strong>” 、 “<strong class='u-colorWarning'>awesome</strong>” 、 “<strong class='u-colorWarning'>kung.fu</strong>” （包含在 “<strong class='u-colorSuccess'>action</strong>” ）等。如果是现场表演，你可以添加 “<strong class='u-colorSuccess'>live</strong>”。</li>

    <li>仅添加电影本身的信息，而不是某个具体版本的信息</strong>。严禁使用 “<strong class='u-colorWarning'>remux</strong>” 、 “<strong class='u-colorWarning'>encode</strong>” 、 “<strong class='u-colorWarning'>blu.ray</strong>” 、 “<strong class='u-colorWarning'>eac3to</strong>” 等。请记住，他们仅用以标明同一电影的其他版本，本身并非标签。</li>

    <li><strong>如果你对<a href='upload.php'>左侧文本框的官方标签</a>有疑问，那就不要添加进去。</strong></li>",

    'upload_title_de' => "该部分规则决定哪些内容可以被发布到本站。",
    'clients_title_de' => "该部分规则决定哪些客户端可以连接到我们的服务器，以及为它们设定的相关条例。",
    'chat_title_de' => "该部分规则请你在前往论坛发帖或交流群发言之前阅读。",
    'tags_title_de' => "该部分规则决定哪些标签可以添加而哪些不能。",
    'collages_title_de' => "该部分规则决定合集的组织和管理形式。",
    'requests_title_de' => "该部分规则决定求种的组织和管理形式。",
    'ratio_title_de' => "该部分规则决定用户在本站做种／下载活动应如何进行。",
    'golden_rules_de' => "该部分规则至关重要，违反它们会导致极为严重的后果。",
    'end' => "分享率规则"
);
