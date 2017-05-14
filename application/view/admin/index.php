<div class="row">
    <div class="col s12 m6">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Memory usage</span>
                <table>
                    <tbody>
                        <tr>
                            <td>Total memory</td>
                            <td><?php echo number_format($this->totalMem, 2); ?> MiB</td>
                        </tr>
                        <tr>
                            <td>Used</td>
                            <td><?php echo number_format($this->memUsagePercentage, 2); ?> %</td>
                        </tr>
                    </tbody>
                </table>
                <div class="progress">
                    <div class="determinate" style="width: <?php echo $this->memUsagePercentage; ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col s12 m3">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Activated users</span>
                <div class="user-count"><?php echo Admin::countActivatedUsers(); ?></div>
            </div>
        </div>
    </div>
    <div class="col s12 m3">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Online users</span>
                <div class="user-count"><?php echo $this->onlineUsers; ?></div>
            </div>
        </div>
    </div>
</div>