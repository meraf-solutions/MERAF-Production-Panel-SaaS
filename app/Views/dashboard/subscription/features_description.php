        <!-- Features Description -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><?= lang('Pages.Features_Description') ?></h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="features-description" class="table table-center table-striped bg-white mb-0">
                        <thead>
                            <tr>
                                <th><?= lang('Pages.Feature') ?></th>
                                <th><?= lang('Pages.Description') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($packageModules as $module) : ?>
                                <?php if($module['is_enabled'] === 'yes') : ?>
                                    <tr>
                                        <td style=""><?= lang('Pages.' . $module['module_name']) ?></td>
                                        <td><?= lang('Pages.' . $module['module_description']) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>