<?php
// session_start();
require_once('connect.php');
require_once('header.php');

if (isset($_GET['comments_id'])) {
    $id = htmlspecialchars($_GET['comments_id']);

    // 確認是不是這位教授可以去評論的學生
    $stmt = $conn->prepare('SELECT * FROM `comments` WHERE `id` = ? && `prof_id` = ? AND (`status` = 0 OR `status` = 1)');
    $stmt->bind_param('ii', $id, $_SESSION['prof_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $rows = mysqli_num_rows($result);
    if ($rows == 1) {
?>
    <script>
        function cancelComment() {
            // if (confirm('確定要回上一頁嗎？若選擇確定，剛剛新增或修改的動作將不被記錄。') == true) {
                window.location.href = 'index.php';
            // }
        }

        function collectComments(currentID) {
            // `otherCommentsIndexRecord`：type(id) 記錄有哪些`其他評論`
            let otherComments = $('.tab-content .other-comment');
            let otherCommentsIndexRecord = '';
            for (let i = 0, count = 0; i < otherComments.length; i++) {
                let splitSymbol = (count == 0) ? '' : ',';
                if (otherComments.eq(i).val() !== '') {
                    otherCommentsIndexRecord += splitSymbol + otherComments.eq(i).data('index');
                    count++;
                }
            }

            let result = '';
            result += `
                <div class="form-row">
                    <input type="hidden" name="comments_id" value="${currentID}">
                    <input type="hidden" name="other_comment_index" value="${otherCommentsIndexRecord}">
            `;

            // `tmp`：type(html) 記錄有哪些`[各類]評論`
            // 並加進`result`
            let commentsPane = $('.tab-content .tab-pane');
            for (let i = 0; i < commentsPane.length; i++) {
                let commentEg = commentsPane.eq(i).find('.comments-eg');
                let tmp = ``;
                for (let j = 0; j < commentEg.length; j++) {
                    if (commentEg.eq(j).find('input').prop('checked')) {
                        let detail = commentEg.eq(j).find('label').text();
                        let id = i + '-' + (j+1);
                        tmp += `
                            <div class="comments-eg">
                                <input type="checkbox" name="comments_codes[]" value="${id}" checked style="display: none;">
                                <label class="btn btn-comments-eg text-left" for="comments-${id}" style="pointer-events: none;">${detail}</label>
                            </div>
                        `;
                    }
                }
                let otherCommentDetail = commentsPane.eq(i).find('.form-group textarea').val();
                if (otherCommentDetail !== '') {
                    tmp += `
                        <div class="form-group">
                            <textarea class="form-control" name="other_comment[]" id="other-comment-${i}" rows="3" readonly>${otherCommentDetail}</textarea>
                        </div>
                    `;
                }

                if (tmp !== '') {
                    let title = commentsPane.eq(i).attr('aria-labelledby').split('：')[1];
                    result += `
                        <div class="form-group col-md-12">
                            <label style="display: block;">${title}</label>
                            ${tmp}
                        </div>
                    `;
                }
            }
            result += `</div>`;

            $('#form-check-comments').html(result);
        }
    </script>

    <div class="modal fade" id="checkModal" tabindex="-1" role="dialog" aria-labelledby="checkModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">評論確認</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form-check-comments" action="comments.php" method="POST">
                        <!-- <div class="form-row">
                            <input type="hidden" name="comments_id" value="5">
                            <input type="hidden" name="other_comment_index" value="0,2">
                            <div class="form-group col-md-12">
                                <label style="display: block;">研究動機</label>
                                <div class="comments-eg">
                                    <input type="checkbox" name="comments_codes[]" value="0-1" checked style="display: none;">
                                    <label class="btn btn-comments-eg text-left" for="comments-0-1" style="pointer-events: none;">研究動機不明</label>
                                </div>
                                <div class="comments-eg">
                                    <input type="checkbox" name="comments_codes[]" value="0-2" checked style="display: none;">
                                    <label class="btn btn-comments-eg text-left" for="comments-0-2" style="pointer-events: none;">預期貢獻應包含學術上貢獻與實務上貢獻</label>
                                </div>
                                <div class="comments-eg">
                                    <input type="checkbox" name="comments_codes[]" value="0-3" checked style="display: none;">
                                    <label class="btn btn-comments-eg text-left" for="comments-0-3" style="pointer-events: none;">研究主題之重要性與可行性應明確交代</label>
                                </div>
                                <div class="form-group">
                                    <textarea class="form-control" name="other_comment[]" id="other-comment-0" rows="3" readonly>000研究動機研究方法</textarea>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <label style="display: block;">研究方法</label>
                                <div class="comments-eg">
                                    <input type="checkbox" name="comments_codes[]" value="2-1" checked style="display: none;">
                                    <label class="btn btn-comments-eg text-left" for="comments-2-1" style="pointer-events: none;">研究方法並未說明清楚</label>
                                </div>
                                <div class="form-group">
                                    <textarea class="form-control" name="other_comment[]" id="other-comment-2" rows="3" readonly>222研究方法研究方法</textarea>
                                </div>
                            </div>
                        </div> -->
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">繼續修改</button>
                    <button type="button" class="btn btn-success" onclick="$('#form-check-comments').submit();">確定儲存</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="quitCommentModal" tabindex="-1" role="dialog" aria-labelledby="checkModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">訊息視窗</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    確定要回上一頁嗎？<br>若選擇確定，剛剛新增或修改的動作將<mark class="text-danger font-weight-bold">不被記錄</mark>。
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-light" onclick="cancelComment();">確定</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <b>國立中正大學資管所&醫管所論文提案書評論系統</b>
                <form>
                    <input type="button" class="btn btn-sm btn-light" value="<?php echo $_SESSION['account']; ?>" disabled>
                    <input type="button" class="btn btn-sm btn-secondary" value="回上一頁" data-toggle="modal" data-target="#quitCommentModal" onclick="return false; /*cancelComment();">
                    <input type="button" class="btn btn-sm btn-success" value="結束評論" data-toggle="modal" data-target="#checkModal" onclick="collectComments(<?php echo $id; ?>)">
                </form>
            </div>
<?php
        // 抓資料，如果有紀錄的話
        $stmt = $conn->prepare('SELECT * FROM `comments` WHERE `id` = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result_data_comments = $stmt->get_result();
        $stmt->close();
        $rows_data_comments = mysqli_num_rows($result_data_comments);
        $comments_details['other_comment'] = '';
        if ($rows_data_comments == 1) {
            $comments_details = mysqli_fetch_assoc($result_data_comments);
        }

        // 產生範例評論
        $stmt = $conn->prepare('SELECT * FROM `comments_codes`');
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $rows = mysqli_num_rows($result);
        if ($rows > 0) {
            $arr_comments_all = array();
            $arr_comments_category = array();
            for ($i = 0; $i < $rows; $i++) {
                $comments_eg = mysqli_fetch_assoc($result);

                // 把`類別陣列`丟到`總陣列`
                if ($i != 0 && $comments_eg['sub'] == 0) {
                    array_push($arr_comments_all, $arr_comments_category);

                    // 因為陣列是全域變數，所以要清空並重設
                    unset($arr_comments_category);
                    $arr_comments_category = array();
                }

                // 把各類別細項丟到`細項陣列`
                $arr_comment_details = array($comments_eg['main'], $comments_eg['sub'], $comments_eg['name']);

                // 把`細項陣列`丟到`類別陣列`
                array_push($arr_comments_category, $arr_comment_details);
            }

            // 把最後的那個`類別陣列`丟到`總陣列`
            array_push($arr_comments_all, $arr_comments_category);
        }
        else {
            echo '找不到評論範例。';
            die();
        }
?>
            <div class="card-body">
                <div class="alert alert-primary" role="alert">
                    點選以選取評論範例。
                </div>
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
<?php
        for ($i = 0; $i < count($arr_comments_all); $i++) {
            $m = $arr_comments_all[$i][0][0]; // `main`
            $s = $arr_comments_all[$i][0][1]; // `sub`
            $n = $arr_comments_all[$i][0][2]; // `name`
            $active = ($m == 0) ? 'active' : '';
            $aria_selected = ($m == 0) ? 'true' : 'false';
?>
                        <a class="nav-item nav-link <?php echo $active; ?>" id="nav-<?php echo $m; ?>-tab" data-toggle="tab" href="#nav-<?php echo $m; ?>" role="tab" aria-controls="分頁：<?php echo $n; ?>" aria-selected="<?php echo $aria_selected; ?>"><?php echo $n; ?></a>
<?php
        }
?>
                    </div>
                </nav>
                <div class="tab-content mt-3" id="nav-tabContent">
<?php
        for ($i = 0; $i < count($arr_comments_all); $i++) {
            $active = ($i == 0) ? 'active' : '';
?>
                    <div class="tab-pane fade show <?php echo $active; ?>" id="nav-<?php echo $i; ?>" role="tabpanel" aria-labelledby="分頁：<?php echo $arr_comments_all[$i][0][2]; ?>">
<?php
            for ($j = 1; $j < count($arr_comments_all[$i]); $j++) {
                $f = $arr_comments_all[$i][$j][0] . '-' . $arr_comments_all[$i][$j][1];
                $n = $arr_comments_all[$i][$j][2];
?>
                        <div class="comments-eg">
                            <input type="checkbox" id="comments-<?php echo $f; ?>" name="comments_codes[]" style="display: none;">
                            <label class="btn btn-comments-eg text-left" for="comments-<?php echo $f; ?>"><?php echo $n; ?></label>
                        </div>
<?php
            }
?>
                        <hr>
                        <div class="form-group">
                            <textarea class="form-control other-comment" name="other_comment" data-index="<?php echo $i; ?>" rows="3" placeholder="<?php echo $arr_comments_all[$i][0][2]; ?>相關的其他評論 . . . . . ."></textarea>
                        </div>
                    </div>
<?php
        }
?>
                </div>
            </div>
            <div class="card-footer text-right">
                <form>
                    <input type="button" class="btn btn-sm btn-secondary" value="回上一頁" onclick="cancelComment()">
                    <input type="button" class="btn btn-sm btn-success" value="結束評論" data-toggle="modal" data-target="#checkModal" onclick="collectComments(<?php echo $id; ?>)">
                </form>
            </div>
        </div>
    </div>
<?php
        if ($rows_data_comments == 1) {
?>
            <script>
                let comments = '<?php echo $comments_details['comment']; ?>'.split(',');
                for (let i  = 0; i < comments.length; i++) {
                    $('#comments-' + comments[i]).prop("checked", true);
                }

                let otherComments = '<?php echo $comments_details["other_comment"]; ?>'.split('@,|,@');
                console.log(otherComments);
                for (let i = 0; i < otherComments.length; i++) {
                    otherCommentsDetails = otherComments[i].split('@-|-@');
                    console.log(otherCommentsDetails)
                    $('.other-comment[data-index="' + otherCommentsDetails[0] + '"]').text(otherCommentsDetails[1]);
                }
            </script>
<?php
        }
    }
    else {
        echo '這不是教授可以評論的學生喔。';
        header("refresh: 0; url=./index.php", true, 301);
        exit();
    }
}
else {
    echo '請選擇評論學生。';
    header("refresh: 0; url=./index.php", true, 301);
    exit();
}

require_once('footer.php');
