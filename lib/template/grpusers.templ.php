<!-- Template -->
<script type="text/template" id="users_table">
    <h2><%= userID %></h2>
</script>
<!-- End template -->


<script>
    window.wp_GrpUsers = {};
    var wpm_user = window.wp_GrpUsers;
    wpm_user.users_in_group_info = <?= $users_in_group_info ?>;
    wpm_user.users_available_info = <?= $users_available_info ?>;
    wpm_user.group_id = <?= $group_id ?>;
</script>