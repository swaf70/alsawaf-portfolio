</main>
</div>

<div class="modal" id="confirmModal" aria-hidden="true">
    <div class="modal-overlay"></div>
    <div class="modal-box modal-sm">
        <div class="modal-icon danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3 id="confirmTitle">تأكيد الحذف</h3>
        <p id="confirmText">هل أنت �
تأكد �
ن إت�
ا�
 هذه الع�
لية؟</p>
        <div class="modal-btns">
            <button class="btn btn-danger" id="confirmOk"><i class="fa-solid fa-trash"></i> نع�
، احذف</button>
            <button class="btn btn-ghost" data-close>إلغاء</button>
        </div>
    </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<?php if (defined('INLINE_ASSETS')): ?>
<script><?= $INLINE['js_admin'] ?? '' ?></script>
<?php else: ?>
<script src="<?= e(base_url('admin/assets/js/admin.js?v=3')) ?>"></script>
<?php endif; ?>
</body>
</html>