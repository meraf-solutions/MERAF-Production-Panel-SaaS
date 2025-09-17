<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= lang('Pages.' . ucwords($subsection ?? $section)) ?></h5>

        <nav aria-label="breadcrumb" class="d-inline-block mt-2 mt-sm-0">
            <ul class="breadcrumb bg-transparent rounded mb-0 p-0">
                <li class="breadcrumb-item text-capitalize"><a href="<?= base_url() ?>"><?= lang('Pages.Home') ?></a></li>

                <li class="breadcrumb-item text-capitalize"><?= lang('Pages.Admin') ?></li>

                <?php if(isset($section)) : ?>
                    <li class="breadcrumb-item text-capitalize <?= !isset($subsection) ? 'active' : '' ?>" <?= !isset($subsection) ? 'aria-current="page"' : '' ?>><?= lang('Pages.' . ucwords($section)) ?></li>
                <?php endif; ?>

                <?php if(isset($subsection)) : ?>
                    <li class="breadcrumb-item text-capitalize active" aria-current="page"><?= lang('Pages.' . ucwords($subsection)  ) ?></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?= $this->endSection() //End section('heading')?>

<?= $this->section('content') ?>
    <div class="row g-2 mt-1">
        <div class="col-xl-3 col-lg-3 col-md-4 col-12 mt-4">
            <div class="card rounded border-0 shadow p-4">
                <ul class="nav nav-pills nav-link-soft nav-justified flex-column bg-white-color mb-0" id="pills-tab" role="tablist">
                    <?php $i = 0; foreach($cronjobLogData as $logType => $logs) : ?>
                        <li class="nav-item <?= $i !== 0 ? 'mt-2' : ''?>">
                            <a class="nav-link rounded <?= $i === 0 ? 'active' : ''?>" id="<?= str_replace('-','_',$logType) ?>-tab" data-bs-toggle="pill" href="#<?= str_replace('-','_',$logType) ?>" role="tab" aria-controls="<?= str_replace('-','_',$logType) ?>" aria-selected="false">
                                <div class="text-start px-3">
                                    <span class="mb-0"><?= ucwords(str_replace(['log-', '-'], ['', ' '], $logType)) ?></span>
                                </div>
                            </a><!--end nav link-->
                        </li><!--end nav item-->
                    <?php $i++; endforeach; ?>
                </ul><!--end nav pills-->
            </div>
        </div><!--end col-->

        <div class="col-xl-9 col-lg-9 col-md-8 col-12 mt-4">
            <div class="tab-content rounded-0 shadow-0" id="pills-tabContent">
                <?php $i = 0; foreach($cronjobLogData as $logType => $logs) : ?>
                    <div class="tab-pane fade <?= $i === 0 ? 'show active' : 'rounded'?>" id="<?= str_replace('-','_',$logType) ?>" role="tabpanel" aria-labelledby="<?= str_replace('-','_',$logType) ?>-tab">
                        <div class="col-lg-12 mb-3">
                            <div class="card border-0 rounded shadow p-4">
                                <h5 class="mb-0"><?= ucwords(str_replace(['log-', '-'], ['', ' '], $logType)) ?></h5>
                            </div>
                        </div>

                        <div class="col-lg-12 mb-3">
                            <div class="card border-0 rounded shadow p-0">
                                <div class="table-responsive p-4">
                                    <table id="<?= $logType ?>-table" class="table table-center table-striped bg-white mb-0">
                                        <thead>
                                            <tr>
                                                <th><?= lang('Pages.Task') ?></th>
                                                <th><?= lang('Pages.Type') ?></th>
                                                <th><?= lang('Pages.Start_Time') ?></th>
                                                <th><?= lang('Pages.Durations') ?></th>
                                                <th><?= lang('Pages.Output') ?></th>
                                                <th><?= lang('Pages.Error') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody id="<?= $logType ?>-tbody">
                                            <!-- Initial rows loaded with the view -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>                    
                    </div>
                <?php $i++; endforeach; ?>
            </div>
        </div><!--end col-->
    </div><!--end row-->
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/datatables.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            <?php foreach($cronjobLogData as $logType => $logs) { ?>
                $('#<?= $logType ?>-table').DataTable({
                    "data": <?= json_encode($logs) ?>,
                    "autoWidth": false,
                    "width": "100%",
                    "pagingType": "first_last_numbers",
                    "columns": [
                        { 
                            "data": "task",
                            "visible": false
                            // "render": function(data) {
                            //     return data.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            // }
                        },
                        { "data": "type", "visible": false },
                        { 
                            "data": "start",
                            "render": function(data) {
                                return formatDateTime(data);
                            }
                        },
                        { 
                            "data": "duration",
                            "className": "text-muted",
                        },
                        { 
                            "data": "output",
                            "render": function(data) {
                                try {
                                    const jsonData = JSON.parse(data);
                                    return `<span class="text-${jsonData.success ? 'success' : 'danger'}">${jsonData.msg}</span>`;
                                } catch (e) {
                                    return data;
                                }
                            }
                        },
                        { 
                            "data": "error",
                            "render": function(data) {
                                if (data === 'N;' || !data) {
                                    return '<span class="text-success">No Error</span>';
                                }
                                return `<span class="text-danger">${data}</span>`;
                            }
                        }
                    ],
                    "language": {
                        "paginate": {
                            "first": '<i class="mdi mdi-chevron-double-left"></i>',
                            "last": '<i class="mdi mdi-chevron-double-right"></i>',
                            "next": "&#8594;",
                            "previous": "&#8592;"
                        },
                        "search": "<?= lang('Pages.Search') ?>:",
                        "lengthMenu": "<?= lang('Pages.DT_lengthMenu') ?>",
                        "loadingRecords": "<?= lang('Pages.Loading_button') ?>",
                        "info": '<?= lang('Pages.DT_info') ?>',
                        "infoEmpty": '<?= lang('Pages.DT_infoEmpty') ?>',
                        "zeroRecords": '<?= lang('Pages.DT_zeroRecords') ?>',
                        "emptyTable": '<?= lang('Pages.DT_emptyTable') ?>'
                    },
                    "fixedHeader": {
                        "header": true,
                        "footer": true
                    },
                    "order": [[2, "desc"]], // Sort by start time descending
                    "pageLength": 25,
                    "responsive": true
                });
            <?php } ?>
        });         
    </script>
<?= $this->endSection() //End section('scripts')?>