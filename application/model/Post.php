<?php

use Famework\Registry\Famework_Registry;

class Post implements JsonSerializable {

    const DB_TABLE = 'user_posts';

    use Hasmeta;

    public static function getById($postID) {
        $stm = Famework_Registry::getDb()->prepare('SELECT * FROM user_posts WHERE id = ? LIMIT 1');
        $stm->execute(array($postID));

        $row = $stm->fetch();

        if (empty($row)) {
            return NULL;
        }

        // return new post object with preload meta
        return new Post($row['id'], $row);
    }

    public static function generateGid($usergid, $timestamp) {
        return hash('sha512', $usergid . '@' . $timestamp);
    }

    /**
     * Insert a post / comment
     * @param Currentuser $user
     * @param string $content
     * @param array $groupIDs An array of groupIDs to post to
     * @param int $postID The ID of the post to comment to
     * @return int The post's id
     */
    public static function insert(Currentuser $user, $content, array $groupIDs, $postID = NULL) {
        $db = Famework_Registry::getDb();
        $stm = $db->prepare('INSERT INTO user_posts (gid, user_id, post_id, content) VALUES (:gid, :uid, :pid, :content)');
        $gid = self::generateGid($user->getGid(), time());
        $userID = $user->getId();
        $stm->bindParam(':gid', $gid);
        $stm->bindParam(':uid', $userID, PDO::PARAM_INT);
        $stm->bindParam(':pid', $postID, PDO::PARAM_INT);
        $stm->bindParam(':content', $content);
        $stm->execute();
        $thisPostID = $db->lastInsertId();

        // public posts are sent to all groups
        if (in_array($user->getPublicGroupId(), $groupIDs)) {
            foreach ($user->getGroupOverview() as $id => $name) {
                if (!in_array($id, $groupIDs)) {
                    $groupIDs[] = $id;
                }
            }
        }

        $stmt = $db->prepare('INSERT IGNORE INTO user_posts_data (post_id, group_id) VALUES (:pid, :grip)');
        foreach ($groupIDs as $grip) {
            $stmt->bindParam(':pid', $thisPostID, PDO::PARAM_INT);
            $stmt->bindParam(':grip', $grip, PDO::PARAM_INT);
            $stmt->execute();
        }

        return $thisPostID;
    }

    /**
     * @var PDO
     */
    private $_db;
    private $_id;

    public function __construct($postID, $meta = NULL) {
        $this->_id = $postID;
        $this->_meta = $meta;
        $this->_db = Famework_Registry::getDb();
    }

    public function isMajorPost() {
        return ($this->getWhatever('post_id') === NULL ? TRUE : FALSE);
    }

    /**
     * @var Post
     */
    private $_motherpost = NULL;

    public function getMotherPost() {
        if (!isset($this->_motherpost) && !empty($this->getWhatever('post_id'))) {
            $this->_motherpost = Post::getById($this->getWhatever('post_id'));
        }

        return $this->_motherpost;
    }

    public function isSubComment() {
        // post is major
        if ($this->isMajorPost() === TRUE) {
            return FALSE;
        }
        // post is normal comment, because mother is the actual post
        if ($this->getMotherPost()->isMajorPost()) {
            return FALSE;
        }

        return TRUE;
    }

    public function isNormalComment() {
        // post is major
        if ($this->isMajorPost() === TRUE) {
            return FALSE;
        }
        // is sub comment?
        if ($this->isSubComment() === TRUE) {
            return FALSE;
        }

        return TRUE;
    }

    public function countFavs() {
        $stm = $this->_db->prepare('SELECT count(1) count FROM (SELECT * FROM user_posts_favs WHERE post_id = ?) x');
        $stm->execute(array($this->getId()));

        return (int) $stm->fetch()['count'];
    }

    public function getId() {
        return (int) $this->getWhatever('id');
    }

    public function getOwnerId() {
        return (int) $this->getWhatever('user_id');
    }

    public function getCreationTime() {
        return $this->getWhatever('datetime');
    }

    public function getContent() {
        return $this->getWhatever('content');
    }

    private $_groupIds;

    public function getGroupIds() {
        if ($this->_groupIds === NULL) {
            $stm = $this->_db->prepare('SELECT * FROM user_posts_data WHERE post_id = :pid');
            $id = $this->getId();
            $stm->bindParam(':pid', $id, PDO::PARAM_INT);
            $stm->execute();

            $groupIDs = array();

            foreach ($stm->fetchAll() as $row) {
                $groupIDs[] = (int) $row['group_id'];
            }

            $this->_groupIds = $groupIDs;
        }

        return $this->_groupIds;
    }

    public function getEntireComments() {
        $res = array();

        $comments = $this->getDirectComments();

        foreach ($comments as $comment) {
            $subcomments = $comment->getDirectComments();
            $res[] = array('comment' => $comment, 'subcomments' => $subcomments);
        }

        return $res;
    }

    public function getDirectComments() {
        if ($this->isSubComment() === TRUE) {
            return NULL;
        }

        $stm = $this->_db->prepare('SELECT id FROM user_posts WHERE post_id = ?');
        $stm->execute(array($this->getId()));

        $res = array();

        foreach ($stm->fetchAll() as $row) {
            $res[] = Post::getById($row['id']);
        }

        return $res;
    }

    public function remove() {
        $stm = $this->_db->prepare('DELETE FROM user_posts WHERE id = ? LIMIT 1');
        $stm->execute(array($this->getId()));
    }

    public function render(Currentuser $user, $addRedir = FALSE) {
        if ($this->isMajorPost()) {
            $this->renderAsPost($user, FALSE, $addRedir);
        } elseif ($this->isNormalComment()) {
            $this->renderAsComment($user, $addRedir);
        } else {
            $this->renderAsSubcomment($user, $addRedir);
        }
    }

    public function renderPublic($user) {
        $this->renderAsPost($user, TRUE);
    }

    private function renderAsPost($thisuser, $public = FALSE, $addRedir = FALSE) {
        if (!empty($thisuser)) {
            $user = new Otheruser($this->getOwnerId(), $thisuser->getId());
        } else {
            $user = new Otheruser($this->getOwnerId(), NULL);
        }
        $datetime = Dateutils::getPostDiff(new DateTime($this->getCreationTime()));
        ?>
        <div class="row dashboard_post_header">
            <div class="col one center_txt">
                <img src="<?php echo $user->getPictureUrl(Currentuser::PIC_LARGE); ?>" width="40" height="40" alt="Posting user picture">
            </div>
            <div class="col nine">
                <div class="row dashboard_post_user">
                    <div class="col ten"><a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $user->getFullQualifiedName(); ?>"><?php echo Security::wbrusername(Security::htmloutput($user->getDisplayName()), TRUE); ?></a></div>
                </div>
                <div class="row dashboard_post_time">
                    <div class="col ten text_light">
                        <div class="left text_light"><span class="genericon genericon-time"></span> <?php echo $datetime; ?></div>
                        <?php if (!empty($thisuser) && $thisuser->getId() === $user->getId() && $public === FALSE) : ?>
                            <div class="left text_light inline_margin">&middot;</div>
                            <div class="left text_light inline_margin">
                                <span class="genericon genericon-reply"></span> <?php
                                if (!in_array($user->getPublicGroupId(), $this->getGroupIds())) {
                                    $groups = array();
                                    foreach ($this->getGroupIds() as $grp) {
                                        $groups[] = Security::htmloutput(Group::getNameById($grp));
                                    }
                                    echo implode(', ', $groups);
                                } else {
                                    echo Security::htmloutput(Group::getNameById($user->getPublicGroupId()));
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row dashboard_post_content">
            <div class="col ten">
                <div class="row dashboard_post_text">
                    <p>
                        <?php
                        echo $this->replaceMentions(
                                $this->replaceLinks(
                                        $this->replaceStyle(
                                                Security::htmloutput($this->getContent()))));
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <hr>
        <div class="row dashboard_post_footer">
            <?php if ($public === FALSE): ?>
                <div class="left text_light">
                    <span class="genericon genericon-comment"></span> <a class="noa"><?php echo t('dashboard_post_commentit'); ?></a>
                </div>
                <div class="left text_light inline_margin">&middot;</div>
            <?php endif; ?>
            <div class="left text_light inline_margin" data-toggle="tooltip" data-placement="top" title="<?php echo t('dashboard_post_fav_tooltip'); ?>">
                <?php echo $this->countFavs(); ?> <span class="genericon genericon-star"></span>
            </div>
            <?php if ($public === FALSE): ?>
                <div class="left text_light inline_margin">&middot;</div>
                <div class="left text_light inline_margin">
                    <a class="noa" href="<?php echo $this->getFavData($thisuser, $addRedir)['link'] ?>"><?php echo t($this->getFavData($thisuser, $addRedir)['txt']); ?></a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function renderAsComment(Currentuser $thisuser, $addRedir = FALSE) {
        $user = new Otheruser($this->getOwnerId(), $this->getId());
        $datetime = Dateutils::getPostDiff(new DateTime($this->getCreationTime()));
        ?>
        <div class="row dashboard_post_comment">
            <div class="col one center_txt">
                <img src="<?php echo $user->getPictureUrl(Currentuser::PIC_SMALL); ?>" width="<?php echo Currentuser::PIC_SMALL; ?>" height="<?php echo Currentuser::PIC_SMALL; ?>" alt="Posting user picture">
            </div>
            <div class="col nine">
                <div class="row dashboard_post_comment_content">
                    <div class="col ten">
                        <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $user->getFullQualifiedName(); ?>" class="text_bold"><?php echo Security::wbrusername(Security::htmloutput($user->getDisplayName())); ?></a> <?php echo $this->replaceMentions(Security::htmloutput($this->getContent())); ?>
                    </div>
                </div>
                <div class="row dashboard_post_comment_info">
                    <div class="col ten">
                        <div class="left text_light">
                            <span class="genericon genericon-time"></span> <?php echo $datetime; ?>
                        </div>
                        <div class="left text_light inline_margin">&middot;</div>
                        <div class="left text_light inline_margin" data-toggle="tooltip" data-placement="top" title="<?php echo t('dashboard_post_fav_tooltip'); ?>">
                            <?php echo $this->countFavs(); ?> <span class="genericon genericon-star"></span>
                        </div>
                        <div class="left text_light inline_margin">&middot;</div>
                        <div class="left text_light inline_margin">
                            <a class="noa" href="<?php echo $this->getFavData($thisuser, $addRedir)['link'] ?>"><?php echo t($this->getFavData($thisuser, $addRedir)['txt']); ?></a>
                        </div>
                        <div class="left text_light inline_margin">&middot;</div>
                        <div class="left text_light inline_margin">
                            <span class="genericon genericon-comment"></span> <a class="noa comment_commentit"><?php echo t('dashboard_post_commentit'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function renderAsSubcomment(Currentuser $thisuser, $addRedir = FALSE) {
        $user = new Otheruser($this->getOwnerId(), $this->getId());
        $datetime = Dateutils::getPostDiff(new DateTime($this->getCreationTime()));
        ?>
        <div class="row dashboard_post_subcomment" post-id="<?php echo $this->getId(); ?>">
            <div class="col one center_txt">
                <img src="<?php echo $user->getPictureUrl(Currentuser::PIC_SMALL); ?>" width="20" height="20" alt="Posting user picture">
            </div>
            <div class="col nine">
                <div class="row dashboard_post_comment_content dashboard_post_subcomment_content">
                    <div class="col ten">
                        <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $user->getFullQualifiedName(); ?>" class="text_bold"><?php echo Security::wbrusername(Security::htmloutput($user->getDisplayName())); ?></a> <?php echo $this->replaceMentions(Security::htmloutput($this->getContent())); ?>
                    </div>
                </div>
                <div class="row dashboard_post_comment_info">
                    <div class="col ten">
                        <div class="left text_light">
                            <span class="genericon genericon-time"></span> <?php echo $datetime; ?>
                        </div>
                        <div class="left text_light inline_margin">&middot;</div>
                        <div class="left text_light inline_margin" data-toggle="tooltip" data-placement="top" title="<?php echo t('dashboard_post_fav_tooltip'); ?>">
                            <?php echo $this->countFavs(); ?> <span class="genericon genericon-star"></span>
                        </div>
                        <div class="left text_light inline_margin">&middot;</div>
                        <div class="left text_light inline_margin">
                            <a class="noa" href="<?php echo $this->getFavData($thisuser, $addRedir)['link'] ?>"><?php echo t($this->getFavData($thisuser, $addRedir)['txt']); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render given Post array as Wall
     * @see Currentuser::getWall() for input array structure
     * @param Currentuser $user
     * @param array $posts
     * @param bool $addRedir Add Redirection to user profile after fav action
     */
    public static function renderLikeWall(Currentuser $user, array $posts, $addRedir = FALSE) {
        foreach ($posts as $post) :
            ?>
            <div class="row dashboard_post_container" post-id="<?php echo $post['post']->getId(); ?>">
                <div class="col ten">

                    <?php if ($post['post']->getOwnerId() === $user->getId()) : ?>
                        <div class="dashboard_post_remove">
                            <a onclick="jsconfirm('/<?php echo APPLICATION_LANG; ?>/post/remove/?id=<?php echo $post['post']->getId(); ?>', '<?php echo t('dashboard_post_delete_confirm'); ?>')" class="noa">
                                <span class="genericon genericon-trash"></span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php $post['post']->render($user, $addRedir); ?>

                    <div class="row dashboard_post_comments">
                        <div class="col ten">

                            <?php foreach ($post['comments'] as $comment): ?>
                                <div class="onecomment" post-id="<?php echo $comment['comment']->getId(); ?>">
                                    <?php $comment['comment']->render($user, $addRedir); ?>
                                    <?php
                                    foreach ($comment['subcomments'] as $subcomment) {
                                        $subcomment->render($user, $addRedir);
                                    }
                                    ?>
                                    <div class="row dashboard_post_comments_newsub" style="display: none;">
                                        <div class="col one center_txt">
                                            <img src="<?php echo $user->getPictureUrl(Currentuser::PIC_SMALL); ?>" width="20" height="20" alt="Posting user picture">
                                        </div>
                                        <div class="col nine">
                                            <div class="row">
                                                <div class="col ten">
                                                    <input type="text" class="newsubcomment small_input" placeholder="<?php echo t('dashboard_post_comment_placeholder'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="row dashboard_post_comments_new">
                                <div class="col one center_txt">
                                    <img src="<?php echo $user->getPictureUrl(Currentuser::PIC_SMALL); ?>" width="<?php echo Currentuser::PIC_SMALL; ?>" height="<?php echo Currentuser::PIC_SMALL; ?>" alt="Posting user picture">
                                </div>
                                <div class="col nine">
                                    <div class="row dashboard_post_user">
                                        <div class="col ten">
                                            <input type="text" class="newcomment small_input" placeholder="<?php echo t('dashboard_post_comment_placeholder'); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        endforeach;
    }

    private function replaceMentions($text) {
        if (preg_match_all('/\B@([a-z0-9.]{3,40})/u', $text, $matches)) {
            foreach ($matches[1] as $name) {
                // send a notification
                $user = Otheruser::getLocalByName($name, NULL);
                if ($user !== NULL) {
                    $text = str_replace('@' . $user->getName(), sprintf('<a href="/%s/user/%s">%s</a>', APPLICATION_LANG, $user->getName(), '@' . $user->getName()), $text);
                }
            }
        }
        return $text;
    }

    private function replaceLinks($text) {
        // get youtube links
        $youtubeLinks = $this->getYoutubeVideoLinks($text);

        foreach ($youtubeLinks as $youtubeLink) {
            $text = str_replace($youtubeLink['link'], sprintf('<youtube>%s</youtube>', $youtubeLink['id']), $text);
        }

        // replace all other links
        $text = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@\+\_%()]+[\w-?&;#~=\.\/\@\+\_%()])/i", "<a href=\"$1\" target=\"_blank\">$1</a>", $text);

        foreach ($youtubeLinks as $youtubeLink) {
            $text = str_replace(sprintf('<youtube>%s</youtube>', $youtubeLink['id']), sprintf('<div class="post_video_container"><iframe width="440" height="250" src="https://www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe></div>', $youtubeLink['id']), $text);
        }

        return $text;
    }

    private function replaceStyle($text) {
        // replace markdown bold
        $text = preg_replace('/\*\*(.+)\*\*/', '<b>$1</b>', $text);
        // replace markdown list
        $text = preg_replace_callback('/^\*(.+)[\R\n]/m', function ($reg) {
            return sprintf('<ul><li>%s</li></ul>', trim($reg[1]));
        }, $text);
        // replace markdown h1
        $text = preg_replace('/#\s?(.+)[\R\n]/', '<h4>$1</h4>', $text);
        // replace inline code
        $text = preg_replace('/```(.+)```/', '<code>$1</code>', $text);
        // replace new line
        $text = str_replace("\n", '<br>', $text);
        return $text;
    }

    private function getYoutubeVideoLinks($note) {
        $res = array();
        preg_match_all('/((http:|https:)\/\/[^\s]+)/', $note, $link_array);
        $links = $link_array[0];

        foreach ($links as $link) {
            // check if link is from YouTube
            if (parse_url($link, PHP_URL_HOST) === 'www.youtube.com') {
                // get the video id (/watch?v=XXXXX)
                parse_str(parse_url($link, PHP_URL_QUERY), $params);
                if (isset($params['v']) && preg_match('/^[0-9a-z]+$/ui', $params['v']) === 1) {
                    $res[] = array('id' => $params['v'], 'link' => $link);
                }
            }
        }

        return $res;
    }

    private $_favdata = NULL;

    private function getFavData(Currentuser $user, $addRedir = FALSE) {
        if ($this->_favdata === NULL) {
            if ($user->hasFavourised($this->getId()) === TRUE) {
                $this->_favdata = array('txt' => 'dashboard_post_defavit',
                    'link' => '/' . APPLICATION_LANG . '/post/defav/?id=' . $this->getId());
            } else {
                $this->_favdata = array('txt' => 'dashboard_post_favit',
                    'link' => '/' . APPLICATION_LANG . '/post/fav/?id=' . $this->getId());
            }

            if ($addRedir === TRUE) {
                $majorOwner = (new Otheruser($this->getMajorPost()->getOwnerId(), $user->getId()))->getFullQualifiedName();
                $this->_favdata['link'] .= '&redirectlocation=' . $majorOwner;
            }
        }

        return $this->_favdata;
    }

    public function fav(Currentuser $user) {
        $stm = $this->_db->prepare('INSERT IGNORE INTO user_posts_favs (user_id, post_id) VALUES (?, ?)');
        $stm->execute(array($user->getId(), $this->getId()));
    }

    public function defav(Currentuser $user) {
        $stm = $this->_db->prepare('DELETE FROM user_posts_favs WHERE user_id = ? AND post_id = ?');
        $stm->execute(array($user->getId(), $this->getId()));
    }

    public function getMajorPost() {
        if ($this->isMajorPost()) {
            return $this;
        }

        if ($this->isNormalComment()) {
            return $this->getMotherPost();
        }

        if ($this->isSubComment()) {
            return $this->getMotherPost()->getMotherPost();
        }
    }

    private $_jsonAdditionals = array();

    public function addJsonData($name, $value) {
        $this->_jsonAdditionals[$name] = $value;
    }

    public function jsonSerialize() {
        $res = new stdClass();

        $res->id = $this->getId();
        $res->owner = Otheruser::getLocalById($this->getOwnerId());
        $res->countFavs = $this->countFavs();
        $res->creationTime = (new DateTime($this->getCreationTime()))->getTimestamp();
        $res->body = $this->getContent();

        foreach ($this->_jsonAdditionals as $key => $value) {
            $res->$key = $value;
        }

        return $res;
    }

}
