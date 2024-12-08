define(['jquery', 'bootstrap', 'layer'], function ($, undefined, Layer) {
    var Controller = {
        index: function () {
            // 发帖功能
            $('#createPost').on('click', function() {
                layer.open({
                    type: 1,
                    title: '发布新帖',
                    area: ['90%', '260px'],
                    content: `
                        <div class="p-3">
                            <textarea class="form-control" id="postContent" rows="6" placeholder="分享你的想法..." maxlength="1000"></textarea>
                            <div class="mt-2 small text-muted">
                                <span id="charCount">0</span>/1000
                            </div>
                            <div class="mt-3 text-right">
                                <button class="btn btn-primary px-4" id="submitPost">发布</button>
                            </div>
                        </div>
                    `,
                    success: function(layero) {
                        $('#postContent').on('input', function() {
                            $('#charCount').text(this.value.length);
                        });
                        
                        $('#submitPost').click(function() {
                            var content = $('#postContent').val().trim();
                            if (!content) {
                                layer.msg('请输入内容');
                                return;
                            }
                            
                            $.post('index/createPost', {content: content}, function(res) {
                                if (res.code == 1) {
                                    layer.closeAll();
                                    layer.msg(res.msg);
                                    location.reload();
                                } else {
                                    layer.msg(res.msg);
                                }
                            });
                        });
                    }
                });
            });

            // 点赞功能
            $('.like-btn').on('click', function() {
                var btn = $(this);
                var postId = btn.data('id');
                
                if (btn.hasClass('active')) {
                    layer.msg('已经点过赞了');
                    return;
                }
                
                $.post('index/likePost', {post_id: postId}, function(res) {
                    if (res.code == 1) {
                        var count = parseInt(btn.find('span').text()) + 1;
                        btn.find('span').text(count);
                        btn.addClass('active');
                        layer.msg('点赞成功');
                    } else {
                        layer.msg(res.msg);
                    }
                });
            });

            // 刷新功能
            $('#refresh').on('click', function() {
                location.reload();
            });
        }
    };
    return Controller;
});