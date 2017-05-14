<div class="row">
    <div class="col s12">
        <h1>Users</h1>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <table class="striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>E-Mail</th>
                            <th>Activated</th>
                            <th>Last login</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($this->userData as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo ($user['activated'] == 1 ? '<span class="green-text">YES</span>' : '<span class="red-text">NO</span>'); ?></td>
                                <td><?php echo $user['last_login']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>