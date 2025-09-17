<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('heading') ?>
    <div class="d-md-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= lang('Pages.' . ucwords($subsection ?? $section)) ?></h5>

        <nav aria-label="breadcrumb" class="d-inline-block mt-2 mt-sm-0">
            <ul class="breadcrumb bg-transparent rounded mb-0 p-0">
                <li class="breadcrumb-item text-capitalize"><a href="<?= base_url() ?>"><?= lang('Pages.Home') ?></a></li>

                <?php if(isset($section)) : ?>
                    <li class="breadcrumb-item text-capitalize <?= !isset($subsection) ? 'active' : '' ?>" <?= !isset($subsection) ? 'aria-current="page"' : '' ?>><?= lang('Pages.' . ucwords($section)) ?></li>
                <?php endif; ?>

                <?php if(isset($subsection)) : ?>
                    <li class="breadcrumb-item text-capitalize active" aria-current="page"><?= lang('Pages.' . ucwords($subsection)  ) ?></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?= $this->endSection() //End section('head')?>

<?= $this->section('content') ?>
    <div class="row">
        <div class="col-12">
            <!-- Pricing Section -->
            <?php include_once APPPATH . 'Views/dashboard/subscription/package_pricing.php'; ?>

            <!-- Features Description -->
            <?php include_once APPPATH . 'Views/dashboard/subscription/features_description.php'; ?>

            <!-- FAQ Section -->
            <?php include_once APPPATH . 'Views/dashboard/subscription/package_faq.php'; ?>
        </div>
    </div>
<?= $this->endSection() //End section('content')?>

<?= $this->section('scripts') ?>
    <?php include_once APPPATH . 'Views/includes/dashboard/subscribe-to-package-js.php'; ?>
<?= $this->endSection() //End section('scripts')?>
