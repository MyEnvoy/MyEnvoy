<?php

use Famework\Registry\Famework_Registry;

class Post {

    const DB_TABLE = 'user_posts';

    use Hasmeta;

    public static function getFromId($postID) {
        $stm = Famework_Registry::getDb()->prepare('SELECT * FROM user_posts WHERE id = ? LIMIT 1');
        $stm->execute(array($postID));

        $row = $stm->fetch();

        if (empty($row)) {
            return NULL;
        }

        // return new post object with preload meta
        return new Post($row['id'], $row);
    }

    public static function insert($userID, $groupID, $content, $postID = NULL) {
        $stm = Famework_Registry::getDb()->prepare('INSERT INTO user_posts (user_id, group_id, post_id, content) VALUES (:uid, :gid, :pid, :content)');
        $stm->bindParam(':uid', $userID, PDO::PARAM_INT);
        $stm->bindParam(':gid', $groupID, PDO::PARAM_INT);
        $stm->bindParam(':pid', $postID, PDO::PARAM_INT);
        $stm->bindParam(':content', $content);
        $stm->execute();
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
            $this->_motherpost = Post::getFromId($this->getWhatever('post_id'));
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

    public function getGroupId() {
        return (int) $this->getWhatever('group_id');
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
            $res[] = Post::getFromId($row['id']);
        }

        return $res;
    }

    public function remove() {
        $stm = $this->_db->prepare('DELETE FROM user_posts WHERE id = ? LIMIT 1');
        $stm->execute(array($this->getId()));
    }

    public function render(Currentuser $user) {
        if ($this->isMajorPost()) {
            $this->renderAsPost($user);
        } elseif ($this->isNormalComment()) {
            $this->renderAsComment($user);
        } else {
            $this->renderAsSubcomment($user);
        }
    }

    public function renderPublic(Currentuser $user) {
        $this->renderAsPost($user, TRUE);
    }

    private function renderAsPost(Currentuser $thisuser, $public = FALSE) {
        $user = new Otheruser($this->getOwnerId(), $thisuser->getId());
        $datetime = Dateutils::getPostDiff(new DateTime($this->getCreationTime()));
        ?>
        <div class="row dashboard_post_header">
            <div class="col one">
                <img src="<?php echo $user->getPictureUrl(Currentuser::PIC_LARGE); ?>" width="40" height="40" alt="Posting user picture">
            </div>
            <div class="col nine">
                <div class="row dashboard_post_user">
                    <div class="col ten"><a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $user->getFullQualifiedName(); ?>"><?php echo Security::wbrusername($user->getFullQualifiedName(), TRUE); ?></a></div>
                </div>
                <div class="row dashboard_post_time">
                    <div class="col ten text_light">
                        <div class="left text_light"><span class="genericon genericon-time"></span> <?php echo $datetime; ?></div>
                        <?php if ($thisuser->getId() === $user->getId() && $public === FALSE) : ?>
                            <div class="left text_light inline_margin">&middot;</div>
                            <div class="left text_light inline_margin">
                                <span class="genericon genericon-reply"></span> <?php echo Group::getNameById($this->getGroupId()); ?>
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
                        <?php echo Security::htmloutput($this->getContent()); ?>
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
                    <a class="noa" href="<?php echo $this->getFavData($thisuser)['link'] ?>"><?php echo t($this->getFavData($thisuser)['txt']); ?></a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function renderAsComment(Currentuser $thisuser) {
        $user = new Otheruser($this->getOwnerId(), $this->getId());
        $datetime = Dateutils::getPostDiff(new DateTime($this->getCreationTime()));
        ?>
        <div class="row dashboard_post_comment">
            <div class="col one">
                <img src="<?php echo $user->getPictureUrl(Currentuser::PIC_SMALL); ?>" width="<?php echo Currentuser::PIC_SMALL; ?>" height="<?php echo Currentuser::PIC_SMALL; ?>" alt="Posting user picture">
            </div>
            <div class="col nine">
                <div class="row dashboard_post_comment_content">
                    <div class="col ten">
                        <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $user->getFullQualifiedName(); ?>" class="text_bold"><?php echo Security::wbrusername($user->getFullQualifiedName()); ?></a> <?php echo Security::htmloutput($this->getContent()); ?>
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
                            <a class="noa" href="<?php echo $this->getFavData($thisuser)['link'] ?>"><?php echo t($this->getFavData($thisuser)['txt']); ?></a>
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

    private function renderAsSubcomment(Currentuser $thisuser) {
        $user = new Otheruser($this->getOwnerId(), $this->getId());
        $datetime = Dateutils::getPostDiff(new DateTime($this->getCreationTime()));
        ?>
        <div class="row dashboard_post_subcomment">
            <div class="col one">
                <img src="<?php echo $user->getPictureUrl(Currentuser::PIC_SMALL); ?>" width="20" height="20" alt="Posting user picture">
            </div>
            <div class="col nine">
                <div class="row dashboard_post_comment_content dashboard_post_subcomment_content">
                    <div class="col ten">
                        <a href="/<?php echo APPLICATION_LANG; ?>/user/<?php echo $user->getFullQualifiedName(); ?>" class="text_bold"><?php echo Security::wbrusername($user->getFullQualifiedName()); ?></a> <?php echo Security::htmloutput($this->getContent()); ?>
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
                            <a class="noa" href="<?php echo $this->getFavData($thisuser)['link'] ?>"><?php echo t($this->getFavData($thisuser)['txt']); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private $_favdata = NULL;

    private function getFavData(Currentuser $user) {
        if ($this->_favdata === NULL) {
            if ($user->hasFavourised($this->getId()) === TRUE) {
                $this->_favdata = array('txt' => 'dashboard_post_defavit',
                    'link' => '/' . APPLICATION_LANG . '/post/defav/?id=' . $this->getId());
            } else {
                $this->_favdata = array('txt' => 'dashboard_post_favit',
                    'link' => '/' . APPLICATION_LANG . '/post/fav/?id=' . $this->getId());
            }
        }

        return $this->_favdata;
    }

    public function fav(Currentuser $user) {
        $stm = $this->_db->prepare('INSERT INTO user_posts_favs (user_id, post_id) VALUES (?, ?)');
        $stm->execute(array($user->getId(), $this->getId()));
    }

    public function defav(Currentuser $user) {
        $stm = $this->_db->prepare('DELETE FROM user_posts_favs WHERE user_id = ? AND post_id = ?');
        $stm->execute(array($user->getId(), $this->getId()));
    }

}
