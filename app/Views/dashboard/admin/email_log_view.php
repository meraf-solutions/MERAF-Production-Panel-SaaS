<?= $this->extend('layouts/single_page') ?>

<?= $this->section('content') ?>
        <h2><?= lang('Pages.Details') ?></h2>
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-center table-striped bg-white mb-2">
                    <tbody>
                        <tr>
                            <th scope="row" class="text-start"><?= lang('Pages.Email_ID') ?></th>
                            <td><?= $log['id'] ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start"><?= lang('Pages.To') ?></th>
                            <td><?= esc($log['to']) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start"><?= lang('Pages.From') ?></th>
                            <td><?= esc($log['from']) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start"><?= lang('Pages.Subject') ?></th>
                            <td><?= esc($log['subject']) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start"><?= lang('Pages.Status') ?></th>
                            <td><?= esc($log['status']) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start"><?= lang('Pages.Sent_At') ?></th>
                            <td><?= formatDate(esc($log['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start"><?= lang('Pages.Retries') ?></th>
                            <td><?= esc($log['retries']) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start"><?= lang('Pages.Resent_Count') ?></th>
                            <td><?= esc($log['resent_count']) ?></td>
                        </tr>
                        <?php if (!empty($log['attachments'])): ?>
                            <tr>
                                <th scope="row" class="text-start"><?= lang('Pages.Attachments') ?></th>
                                <td>
                                    <?php
                                    // Decode JSON-encoded attachments
                                    $attachments = json_decode($log['attachments'], true);
                                    if (!empty($attachments) && is_array($attachments)): ?>
                                        <ol>
                                            <?php foreach ($attachments as $attachment): ?>
                                                <li><?= esc($attachment) ?></li>
                                            <?php endforeach; ?>
                                        </ol>
                                    <?php else: ?>
                                        <?= lang('Pages.No_valid_attachments_found') ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($log['response'])): ?>
                            <tr>
                                <th scope="row" class="text-start"><?= lang('Pages.Response') ?></th>
                                <td><?= esc($log['response']) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($log['extra'])): ?>
                            <tr>
                                <th scope="row" class="text-start"><?= lang('Pages.Extra_Information') ?></th>
                                <td><?= esc($log['extra']) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($log['source'])): ?>
                            <tr>
                                <th scope="row" class="text-start"><?= lang('Pages.Source') ?></th>
                                <td><?= esc($log['source']) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th scope="row" class="text-start"><?= lang('Pages.Email_Format') ?></th>
                            <td><?= esc($log['format']) ?></td>
                        </tr>
                        <?php if (!empty($log['headers'])): ?>
                            <tr>
                                <th scope="row" class="text-start"><?= lang('Pages.Headers') ?></th>
                                <td>
                                    <?= nl2br(
                                        preg_replace(
                                            '/\n{2,}/',
                                            "\n",
                                            str_replace(['<pre>', '</pre>'], '', $log['headers'])
                                        )
                                    ) ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>

                <h6><?= lang('Pages.Email_Body') ?></h6>
                <iframe id="email-body-frame" src="<?= $pageUrl . 'body/' . $log['id'] ?>" class="w-100 form-control" style="height: 600px;"></iframe>
            </div>
        </div>

<?= $this->endSection() ?>
