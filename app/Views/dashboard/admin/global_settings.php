<?= $this->extend('layouts/dashboard') ?>

<?php
$languageDirectories = get_product_directories(APPPATH . 'Language');
$language_reference = json_decode(file_get_contents(ROOTPATH . 'public/assets/libs/language-codes.json'), true);
?>

<?= $this->section('head') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .form-check-input.form-check-input {
            width: 48px;
            height: 24px;
            margin-top: 0;                
            background-color: #2f55d4 !important;
            border-color: #2f55d4 !important;
        }
    </style>
<?= $this->endSection() //End section('head')?>

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
    <form class="row" novalidate id="global-settings-form">
        <?php $adminSettings = getMyConfig('', 0); ?>

        <div class="col-12 mt-4 text-center">
            <!-- Menu -->
            <ul class="nav nav-pills shadow flex-column flex-sm-row d-md-inline-flex mb-0 p-1 bg-white-color rounded position-relative overflow-hidden" id="pills-tab" role="tablist">
                <li class="nav-item m-1">
                    <a class="nav-link py-2 px-5 active rounded" id="general-data" data-bs-toggle="pill" href="#generalData" role="tab" aria-controls="general-data" aria-selected="false">
                        <div class="text-center">
                            <h6 class="mb-0"><?= lang('Pages.General') ?></h6>
                        </div>
                    </a><!--end nav link-->
                </li><!--end nav item-->

                <li class="nav-item m-1">
                    <a class="nav-link py-2 px-5 rounded" id="pwa-setup" data-bs-toggle="pill" href="#pwaSetup" role="tab" aria-controls="pwa-setup" aria-selected="false">
                        <div class="text-center">
                            <h6 class="mb-0"><?= lang('Pages.PWA_Settings') ?></h6>
                        </div>
                    </a><!--end nav link-->
                </li><!--end nav item-->

                <li class="nav-item m-1">
                    <a class="nav-link py-2 px-5 rounded" id="push-notification-setup" data-bs-toggle="pill" href="#pushNotificationSetup" role="tab" aria-controls="push-notification-setup" aria-selected="false">
                        <div class="text-center">
                            <h6 class="mb-0"><?= lang('Pages.Push_Notification') ?></h6>
                        </div>
                    </a><!--end nav link-->
                </li><!--end nav item-->
            </ul>   
        </div>

        <div class="col-12 mt-3 mb-3 text-center">
            <button class="mx-auto btn btn-primary" id="global-settings-submit"><i class="uil uil-save"></i> <?= lang('Pages.Save_Settings') ?></button>
        </div>                                

        <div class="col-12 mb-3">
            <div class="tab-content" id="pills-tabContent">
                <!-- General Data -->
                <div class="tab-pane show active" id="generalData" role="tabpanel" aria-labelledby="general-data">
                    <div class="row">
                        <!-- Clear Server Cache -->
                        <div class="col-xl-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">                                                                        
                                <h5 class="mb-4"><?= lang('Pages.Clear_Server_Cache') ?></h5>
                                <small class="text-info mb-1"><?= lang('Pages.clear_server_cache_desc') ?></small>
                                <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm me-2" id="clear-server-cache-btn"><i class="uil uil-trash"></i> <?= lang('Pages.Clear_Cache_Now') ?></a>                                                 
                            </div>
                        </div>

                        <!-- Cache Settings -->
                        <div class="col-lg-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">                                                                        
                                <h5 class="mb-4"><?= lang('Pages.Cache_Settings') ?></h5>

                                <small class="text-info mb-1"><?= lang('Pages.cache_settings_desc') ?></small>

                                <div id="cache-connection-test-container" style="display: none;">
                                    <button type="button" class="btn btn-soft-secondary btn-sm" id="test-cache-connection-btn" data-bs-toggle="modal" data-bs-target="#cacheTestResultModal">
                                        <i class="uil uil-link"></i> <?= lang('Pages.Test_Connection') ?>
                                    </button>
                                </div>
                                
                                <div class="form-group mt-3">
                                    <label class="form-label" for="cacheHandler"><?= lang('Pages.Cache_Handler') ?></label>
                                    <div class="form-icon position-relative">
                                        <i data-feather="database" class="fea icon-sm icons"></i>
                                        <select class="form-select form-control ps-5" id="cacheHandler" name="cacheHandler">
                                            <option value="dummy" <?= $myConfig['cacheHandler'] === 'dummy' ? 'selected' : '' ?>><?= lang('Pages.Cache_Handler_Dummy') ?></option>
                                            <option value="file" <?= $myConfig['cacheHandler'] === 'file' ? 'selected' : '' ?>><?= lang('Pages.Cache_Handler_File') ?></option>
                                            <option value="memcached" <?= $myConfig['cacheHandler'] === 'memcached' ? 'selected' : '' ?>><?= lang('Pages.Cache_Handler_Memcached') ?></option>
                                            <option value="redis" <?= $myConfig['cacheHandler'] === 'redis' ? 'selected' : '' ?>><?= lang('Pages.Cache_Handler_Redis') ?></option>
                                            <option value="predis" <?= $myConfig['cacheHandler'] === 'predis' ? 'selected' : '' ?>><?= lang('Pages.Cache_Handler_Predis') ?></option>
                                            <option value="wincache" <?= $myConfig['cacheHandler'] === 'wincache' ? 'selected' : '' ?>><?= lang('Pages.Cache_Handler_Wincache') ?></option>
                                        </select>
                                    </div>
                                    <small class="text-muted mt-2"><?= lang('Pages.cache_handler_note') ?></small>
                                </div>
                            </div>
                        </div><!--end col-->

                        <!-- Package Price Currency -->
                        <div class="col-xl-6 mb-3">
                            <div class="card rounded shadow p-4 border-0 mb-3">
                                <label class="form-label" for="packageCurrency">
                                    <h5 class="mb-0">
                                        <?= lang('Pages.Package_Price_Currency') ?> <span class="text-danger">*</span>
                                    </h5>
                                </label>
                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <select class="form-select form-control ps-5" id="packageCurrency" name="packageCurrency" required>
                                                <option value=""><?= lang('Pages.Select_Option') ?></option>
                                                <option value="AED" <?= $adminSettings['packageCurrency'] === 'AED' ? 'selected' : '' ?>>United Arab Emirates dirham (د.إ) — AED</option>
                                                <option value="AFN" <?= $adminSettings['packageCurrency'] === 'AFN' ? 'selected' : '' ?>>Afghan afghani (؋) — AFN</option>
                                                <option value="ALL" <?= $adminSettings['packageCurrency'] === 'ALL' ? 'selected' : '' ?>>Albanian lek (L) — ALL</option>
                                                <option value="AMD" <?= $adminSettings['packageCurrency'] === 'AMD' ? 'selected' : '' ?>>Armenian dram (AMD) — AMD</option>
                                                <option value="ANG" <?= $adminSettings['packageCurrency'] === 'ANG' ? 'selected' : '' ?>>Netherlands Antillean guilder (ƒ) — ANG</option>
                                                <option value="AOA" <?= $adminSettings['packageCurrency'] === 'AOA' ? 'selected' : '' ?>>Angolan kwanza (Kz) — AOA</option>
                                                <option value="ARS" <?= $adminSettings['packageCurrency'] === 'ARS' ? 'selected' : '' ?>>Argentine peso ($) — ARS</option>
                                                <option value="AUD" <?= $adminSettings['packageCurrency'] === 'AUD' ? 'selected' : '' ?>>Australian dollar ($) — AUD</option>
                                                <option value="AWG" <?= $adminSettings['packageCurrency'] === 'AWG' ? 'selected' : '' ?>>Aruban florin (Afl.) — AWG</option>
                                                <option value="AZN" <?= $adminSettings['packageCurrency'] === 'AZN' ? 'selected' : '' ?>>Azerbaijani manat (₼) — AZN</option>
                                                <option value="BAM" <?= $adminSettings['packageCurrency'] === 'BAM' ? 'selected' : '' ?>>Bosnia and Herzegovina convertible mark (KM) — BAM</option>
                                                <option value="BBD" <?= $adminSettings['packageCurrency'] === 'BBD' ? 'selected' : '' ?>>Barbadian dollar ($) — BBD</option>
                                                <option value="BDT" <?= $adminSettings['packageCurrency'] === 'BDT' ? 'selected' : '' ?>>Bangladeshi taka (৳) — BDT</option>
                                                <option value="BGN" <?= $adminSettings['packageCurrency'] === 'BGN' ? 'selected' : '' ?>>Bulgarian lev (лв.) — BGN</option>
                                                <option value="BHD" <?= $adminSettings['packageCurrency'] === 'BHD' ? 'selected' : '' ?>>Bahraini dinar (.د.ب) — BHD</option>
                                                <option value="BIF" <?= $adminSettings['packageCurrency'] === 'BIF' ? 'selected' : '' ?>>Burundian franc (Fr) — BIF</option>
                                                <option value="BMD" <?= $adminSettings['packageCurrency'] === 'BMD' ? 'selected' : '' ?>>Bermudian dollar ($) — BMD</option>
                                                <option value="BND" <?= $adminSettings['packageCurrency'] === 'BND' ? 'selected' : '' ?>>Brunei dollar ($) — BND</option>
                                                <option value="BOB" <?= $adminSettings['packageCurrency'] === 'BOB' ? 'selected' : '' ?>>Bolivian boliviano (Bs.) — BOB</option>
                                                <option value="BRL" <?= $adminSettings['packageCurrency'] === 'BRL' ? 'selected' : '' ?>>Brazilian real (R$) — BRL</option>
                                                <option value="BSD" <?= $adminSettings['packageCurrency'] === 'BSD' ? 'selected' : '' ?>>Bahamian dollar ($) — BSD</option>
                                                <option value="BTC" <?= $adminSettings['packageCurrency'] === 'BTC' ? 'selected' : '' ?>>Bitcoin (฿) — BTC</option>
                                                <option value="BTN" <?= $adminSettings['packageCurrency'] === 'BTN' ? 'selected' : '' ?>>Bhutanese ngultrum (Nu.) — BTN</option>
                                                <option value="BWP" <?= $adminSettings['packageCurrency'] === 'BWP' ? 'selected' : '' ?>>Botswana pula (P) — BWP</option>
                                                <option value="BYR" <?= $adminSettings['packageCurrency'] === 'BYR' ? 'selected' : '' ?>>Belarusian ruble (old) (Br) — BYR</option>
                                                <option value="BYN" <?= $adminSettings['packageCurrency'] === 'BYN' ? 'selected' : '' ?>>Belarusian ruble (Br) — BYN</option>
                                                <option value="BZD" <?= $adminSettings['packageCurrency'] === 'BZD' ? 'selected' : '' ?>>Belize dollar ($) — BZD</option>
                                                <option value="CAD" <?= $adminSettings['packageCurrency'] === 'CAD' ? 'selected' : '' ?>>Canadian dollar ($) — CAD</option>
                                                <option value="CDF" <?= $adminSettings['packageCurrency'] === 'CDF' ? 'selected' : '' ?>>Congolese franc (Fr) — CDF</option>
                                                <option value="CHF" <?= $adminSettings['packageCurrency'] === 'CHF' ? 'selected' : '' ?>>Swiss franc (CHF) — CHF</option>
                                                <option value="CLP" <?= $adminSettings['packageCurrency'] === 'CLP' ? 'selected' : '' ?>>Chilean peso ($) — CLP</option>
                                                <option value="CNY" <?= $adminSettings['packageCurrency'] === 'CNY' ? 'selected' : '' ?>>Chinese yuan (¥) — CNY</option>
                                                <option value="COP" <?= $adminSettings['packageCurrency'] === 'COP' ? 'selected' : '' ?>>Colombian peso ($) — COP</option>
                                                <option value="CRC" <?= $adminSettings['packageCurrency'] === 'CRC' ? 'selected' : '' ?>>Costa Rican colón (₡) — CRC</option>
                                                <option value="CUC" <?= $adminSettings['packageCurrency'] === 'CUC' ? 'selected' : '' ?>>Cuban convertible peso ($) — CUC</option>
                                                <option value="CUP" <?= $adminSettings['packageCurrency'] === 'CUP' ? 'selected' : '' ?>>Cuban peso ($) — CUP</option>
                                                <option value="CVE" <?= $adminSettings['packageCurrency'] === 'CVE' ? 'selected' : '' ?>>Cape Verdean escudo ($) — CVE</option>
                                                <option value="CZK" <?= $adminSettings['packageCurrency'] === 'CZK' ? 'selected' : '' ?>>Czech koruna (Kč) — CZK</option>
                                                <option value="DJF" <?= $adminSettings['packageCurrency'] === 'DJF' ? 'selected' : '' ?>>Djiboutian franc (Fr) — DJF</option>
                                                <option value="DKK" <?= $adminSettings['packageCurrency'] === 'DKK' ? 'selected' : '' ?>>Danish krone (kr.) — DKK</option>
                                                <option value="DOP" <?= $adminSettings['packageCurrency'] === 'DOP' ? 'selected' : '' ?>>Dominican peso (RD$) — DOP</option>
                                                <option value="DZD" <?= $adminSettings['packageCurrency'] === 'DZD' ? 'selected' : '' ?>>Algerian dinar (د.ج) — DZD</option>
                                                <option value="EGP" <?= $adminSettings['packageCurrency'] === 'EGP' ? 'selected' : '' ?>>Egyptian pound (EGP) — EGP</option>
                                                <option value="ERN" <?= $adminSettings['packageCurrency'] === 'ERN' ? 'selected' : '' ?>>Eritrean nakfa (Nfk) — ERN</option>
                                                <option value="ETB" <?= $adminSettings['packageCurrency'] === 'ETB' ? 'selected' : '' ?>>Ethiopian birr (Br) — ETB</option>
                                                <option value="EUR" <?= $adminSettings['packageCurrency'] === 'EUR' ? 'selected' : '' ?>>Euro (€) — EUR</option>
                                                <option value="FJD" <?= $adminSettings['packageCurrency'] === 'FJD' ? 'selected' : '' ?>>Fijian dollar ($) — FJD</option>
                                                <option value="FKP" <?= $adminSettings['packageCurrency'] === 'FKP' ? 'selected' : '' ?>>Falkland Islands pound (£) — FKP</option>
                                                <option value="GBP" <?= $adminSettings['packageCurrency'] === 'GBP' ? 'selected' : '' ?>>Pound sterling (£) — GBP</option>
                                                <option value="GEL" <?= $adminSettings['packageCurrency'] === 'GEL' ? 'selected' : '' ?>>Georgian lari (₾) — GEL</option>
                                                <option value="GGP" <?= $adminSettings['packageCurrency'] === 'GGP' ? 'selected' : '' ?>>Guernsey pound (£) — GGP</option>
                                                <option value="GHS" <?= $adminSettings['packageCurrency'] === 'GHS' ? 'selected' : '' ?>>Ghana cedi (₵) — GHS</option>
                                                <option value="GIP" <?= $adminSettings['packageCurrency'] === 'GIP' ? 'selected' : '' ?>>Gibraltar pound (£) — GIP</option>
                                                <option value="GMD" <?= $adminSettings['packageCurrency'] === 'GMD' ? 'selected' : '' ?>>Gambian dalasi (D) — GMD</option>
                                                <option value="GNF" <?= $adminSettings['packageCurrency'] === 'GNF' ? 'selected' : '' ?>>Guinean franc (Fr) — GNF</option>
                                                <option value="GTQ" <?= $adminSettings['packageCurrency'] === 'GTQ' ? 'selected' : '' ?>>Guatemalan quetzal (Q) — GTQ</option>
                                                <option value="GYD" <?= $adminSettings['packageCurrency'] === 'GYD' ? 'selected' : '' ?>>Guyanese dollar ($) — GYD</option>
                                                <option value="HKD" <?= $adminSettings['packageCurrency'] === 'HKD' ? 'selected' : '' ?>>Hong Kong dollar ($) — HKD</option>
                                                <option value="HNL" <?= $adminSettings['packageCurrency'] === 'HNL' ? 'selected' : '' ?>>Honduran lempira (L) — HNL</option>
                                                <option value="HRK" <?= $adminSettings['packageCurrency'] === 'HRK' ? 'selected' : '' ?>>Croatian kuna (kn) — HRK</option>
                                                <option value="HTG" <?= $adminSettings['packageCurrency'] === 'HTG' ? 'selected' : '' ?>>Haitian gourde (G) — HTG</option>
                                                <option value="HUF" <?= $adminSettings['packageCurrency'] === 'HUF' ? 'selected' : '' ?>>Hungarian forint (Ft) — HUF</option>
                                                <option value="IDR" <?= $adminSettings['packageCurrency'] === 'IDR' ? 'selected' : '' ?>>Indonesian rupiah (Rp) — IDR</option>
                                                <option value="ILS" <?= $adminSettings['packageCurrency'] === 'ILS' ? 'selected' : '' ?>>Israeli new shekel (₪) — ILS</option>
                                                <option value="IMP" <?= $adminSettings['packageCurrency'] === 'IMP' ? 'selected' : '' ?>>Manx pound (£) — IMP</option>
                                                <option value="INR" <?= $adminSettings['packageCurrency'] === 'INR' ? 'selected' : '' ?>>Indian rupee (₹) — INR</option>
                                                <option value="IQD" <?= $adminSettings['packageCurrency'] === 'IQD' ? 'selected' : '' ?>>Iraqi dinar (د.ع) — IQD</option>
                                                <option value="IRR" <?= $adminSettings['packageCurrency'] === 'IRR' ? 'selected' : '' ?>>Iranian rial (﷼) — IRR</option>
                                                <option value="IRT" <?= $adminSettings['packageCurrency'] === 'IRT' ? 'selected' : '' ?>>Iranian toman (تومان) — IRT</option>
                                                <option value="ISK" <?= $adminSettings['packageCurrency'] === 'ISK' ? 'selected' : '' ?>>Icelandic króna (kr.) — ISK</option>
                                                <option value="JEP" <?= $adminSettings['packageCurrency'] === 'JEP' ? 'selected' : '' ?>>Jersey pound (£) — JEP</option>
                                                <option value="JMD" <?= $adminSettings['packageCurrency'] === 'JMD' ? 'selected' : '' ?>>Jamaican dollar ($) — JMD</option>
                                                <option value="JOD" <?= $adminSettings['packageCurrency'] === 'JOD' ? 'selected' : '' ?>>Jordanian dinar (د.ا) — JOD</option>
                                                <option value="JPY" <?= $adminSettings['packageCurrency'] === 'JPY' ? 'selected' : '' ?>>Japanese yen (¥) — JPY</option>
                                                <option value="KES" <?= $adminSettings['packageCurrency'] === 'KES' ? 'selected' : '' ?>>Kenyan shilling (KSh) — KES</option>
                                                <option value="KGS" <?= $adminSettings['packageCurrency'] === 'KGS' ? 'selected' : '' ?>>Kyrgyzstani som (сом) — KGS</option>
                                                <option value="KHR" <?= $adminSettings['packageCurrency'] === 'KHR' ? 'selected' : '' ?>>Cambodian riel (៛) — KHR</option>
                                                <option value="KMF" <?= $adminSettings['packageCurrency'] === 'KMF' ? 'selected' : '' ?>>Comorian franc (Fr) — KMF</option>
                                                <option value="KPW" <?= $adminSettings['packageCurrency'] === 'KPW' ? 'selected' : '' ?>>North Korean won (₩) — KPW</option>
                                                <option value="KRW" <?= $adminSettings['packageCurrency'] === 'KRW' ? 'selected' : '' ?>>South Korean won (₩) — KRW</option>
                                                <option value="KWD" <?= $adminSettings['packageCurrency'] === 'KWD' ? 'selected' : '' ?>>Kuwaiti dinar (د.ك) — KWD</option>
                                                <option value="KYD" <?= $adminSettings['packageCurrency'] === 'KYD' ? 'selected' : '' ?>>Cayman Islands dollar ($) — KYD</option>
                                                <option value="KZT" <?= $adminSettings['packageCurrency'] === 'KZT' ? 'selected' : '' ?>>Kazakhstani tenge (₸) — KZT</option>
                                                <option value="LAK" <?= $adminSettings['packageCurrency'] === 'LAK' ? 'selected' : '' ?>>Lao kip (₭) — LAK</option>
                                                <option value="LBP" <?= $adminSettings['packageCurrency'] === 'LBP' ? 'selected' : '' ?>>Lebanese pound (ل.ل) — LBP</option>
                                                <option value="LKR" <?= $adminSettings['packageCurrency'] === 'LKR' ? 'selected' : '' ?>>Sri Lankan rupee (රු) — LKR</option>
                                                <option value="LRD" <?= $adminSettings['packageCurrency'] === 'LRD' ? 'selected' : '' ?>>Liberian dollar ($) — LRD</option>
                                                <option value="LSL" <?= $adminSettings['packageCurrency'] === 'LSL' ? 'selected' : '' ?>>Lesotho loti (L) — LSL</option>
                                                <option value="LYD" <?= $adminSettings['packageCurrency'] === 'LYD' ? 'selected' : '' ?>>Libyan dinar (د.ل) — LYD</option>
                                                <option value="MAD" <?= $adminSettings['packageCurrency'] === 'MAD' ? 'selected' : '' ?>>Moroccan dirham (د.م.) — MAD</option>
                                                <option value="MDL" <?= $adminSettings['packageCurrency'] === 'MDL' ? 'selected' : '' ?>>Moldovan leu (MDL) — MDL</option>
                                                <option value="MGA" <?= $adminSettings['packageCurrency'] === 'MGA' ? 'selected' : '' ?>>Malagasy ariary (Ar) — MGA</option>
                                                <option value="MKD" <?= $adminSettings['packageCurrency'] === 'MKD' ? 'selected' : '' ?>>Macedonian denar (ден) — MKD</option>
                                                <option value="MMK" <?= $adminSettings['packageCurrency'] === 'MMK' ? 'selected' : '' ?>>Burmese kyat (Ks) — MMK</option>
                                                <option value="MNT" <?= $adminSettings['packageCurrency'] === 'MNT' ? 'selected' : '' ?>>Mongolian tögrög (₮) — MNT</option>
                                                <option value="MOP" <?= $adminSettings['packageCurrency'] === 'MOP' ? 'selected' : '' ?>>Macanese pataca (P) — MOP</option>
                                                <option value="MRU" <?= $adminSettings['packageCurrency'] === 'MRU' ? 'selected' : '' ?>>Mauritanian ouguiya (UM) — MRU</option>
                                                <option value="MUR" <?= $adminSettings['packageCurrency'] === 'MUR' ? 'selected' : '' ?>>Mauritian rupee (₨) — MUR</option>
                                                <option value="MVR" <?= $adminSettings['packageCurrency'] === 'MVR' ? 'selected' : '' ?>>Maldivian rufiyaa (.ރ) — MVR</option>
                                                <option value="MWK" <?= $adminSettings['packageCurrency'] === 'MWK' ? 'selected' : '' ?>>Malawian kwacha (MK) — MWK</option>
                                                <option value="MXN" <?= $adminSettings['packageCurrency'] === 'MXN' ? 'selected' : '' ?>>Mexican peso ($) — MXN</option>
                                                <option value="MYR" <?= $adminSettings['packageCurrency'] === 'MYR' ? 'selected' : '' ?>>Malaysian ringgit (RM) — MYR</option>
                                                <option value="MZN" <?= $adminSettings['packageCurrency'] === 'MZN' ? 'selected' : '' ?>>Mozambican metical (MT) — MZN</option>
                                                <option value="NAD" <?= $adminSettings['packageCurrency'] === 'NAD' ? 'selected' : '' ?>>Namibian dollar (N$) — NAD</option>
                                                <option value="NGN" <?= $adminSettings['packageCurrency'] === 'NGN' ? 'selected' : '' ?>>Nigerian naira (₦) — NGN</option>
                                                <option value="NIO" <?= $adminSettings['packageCurrency'] === 'NIO' ? 'selected' : '' ?>>Nicaraguan córdoba (C$) — NIO</option>
                                                <option value="NOK" <?= $adminSettings['packageCurrency'] === 'NOK' ? 'selected' : '' ?>>Norwegian krone (kr) — NOK</option>
                                                <option value="NPR" <?= $adminSettings['packageCurrency'] === 'NPR' ? 'selected' : '' ?>>Nepalese rupee (₨) — NPR</option>
                                                <option value="NZD" <?= $adminSettings['packageCurrency'] === 'NZD' ? 'selected' : '' ?>>New Zealand dollar ($) — NZD</option>
                                                <option value="OMR" <?= $adminSettings['packageCurrency'] === 'OMR' ? 'selected' : '' ?>>Omani rial (ر.ع.) — OMR</option>
                                                <option value="PAB" <?= $adminSettings['packageCurrency'] === 'PAB' ? 'selected' : '' ?>>Panamanian balboa (B/.) — PAB</option>
                                                <option value="PEN" <?= $adminSettings['packageCurrency'] === 'PEN' ? 'selected' : '' ?>>Sol (S/) — PEN</option>
                                                <option value="PGK" <?= $adminSettings['packageCurrency'] === 'PGK' ? 'selected' : '' ?>>Papua New Guinean kina (K) — PGK</option>
                                                <option value="PHP" <?= $adminSettings['packageCurrency'] === 'PHP' ? 'selected' : '' ?>>Philippine peso (₱) — PHP</option>
                                                <option value="PKR" <?= $adminSettings['packageCurrency'] === 'PKR' ? 'selected' : '' ?>>Pakistani rupee (₨) — PKR</option>
                                                <option value="PLN" <?= $adminSettings['packageCurrency'] === 'PLN' ? 'selected' : '' ?>>Polish złoty (zł) — PLN</option>
                                                <option value="PRB" <?= $adminSettings['packageCurrency'] === 'PRB' ? 'selected' : '' ?>>Transnistrian ruble (р.) — PRB</option>
                                                <option value="PYG" <?= $adminSettings['packageCurrency'] === 'PYG' ? 'selected' : '' ?>>Paraguayan guaraní (₲) — PYG</option>
                                                <option value="QAR" <?= $adminSettings['packageCurrency'] === 'QAR' ? 'selected' : '' ?>>Qatari riyal (ر.ق) — QAR</option>
                                                <option value="RON" <?= $adminSettings['packageCurrency'] === 'RON' ? 'selected' : '' ?>>Romanian leu (lei) — RON</option>
                                                <option value="RSD" <?= $adminSettings['packageCurrency'] === 'RSD' ? 'selected' : '' ?>>Serbian dinar (рсд) — RSD</option>
                                                <option value="RUB" <?= $adminSettings['packageCurrency'] === 'RUB' ? 'selected' : '' ?>>Russian ruble (₽) — RUB</option>
                                                <option value="RWF" <?= $adminSettings['packageCurrency'] === 'RWF' ? 'selected' : '' ?>>Rwandan franc (Fr) — RWF</option>
                                                <option value="SAR" <?= $adminSettings['packageCurrency'] === 'SAR' ? 'selected' : '' ?>>Saudi riyal (ر.س) — SAR</option>
                                                <option value="SBD" <?= $adminSettings['packageCurrency'] === 'SBD' ? 'selected' : '' ?>>Solomon Islands dollar ($) — SBD</option>
                                                <option value="SCR" <?= $adminSettings['packageCurrency'] === 'SCR' ? 'selected' : '' ?>>Seychellois rupee (₨) — SCR</option>
                                                <option value="SDG" <?= $adminSettings['packageCurrency'] === 'SDG' ? 'selected' : '' ?>>Sudanese pound (ج.س.) — SDG</option>
                                                <option value="SEK" <?= $adminSettings['packageCurrency'] === 'SEK' ? 'selected' : '' ?>>Swedish krona (kr) — SEK</option>
                                                <option value="SGD" <?= $adminSettings['packageCurrency'] === 'SGD' ? 'selected' : '' ?>>Singapore dollar ($) — SGD</option>
                                                <option value="SHP" <?= $adminSettings['packageCurrency'] === 'SHP' ? 'selected' : '' ?>>Saint Helena pound (£) — SHP</option>
                                                <option value="SLL" <?= $adminSettings['packageCurrency'] === 'SLL' ? 'selected' : '' ?>>Sierra Leonean leone (Le) — SLL</option>
                                                <option value="SOS" <?= $adminSettings['packageCurrency'] === 'SOS' ? 'selected' : '' ?>>Somali shilling (Sh) — SOS</option>
                                                <option value="SRD" <?= $adminSettings['packageCurrency'] === 'SRD' ? 'selected' : '' ?>>Surinamese dollar ($) — SRD</option>
                                                <option value="SSP" <?= $adminSettings['packageCurrency'] === 'SSP' ? 'selected' : '' ?>>South Sudanese pound (£) — SSP</option>
                                                <option value="STN" <?= $adminSettings['packageCurrency'] === 'STN' ? 'selected' : '' ?>>São Tomé and Príncipe dobra (Db) — STN</option>
                                                <option value="SYP" <?= $adminSettings['packageCurrency'] === 'SYP' ? 'selected' : '' ?>>Syrian pound (£) — SYP</option>
                                                <option value="SZL" <?= $adminSettings['packageCurrency'] === 'SZL' ? 'selected' : '' ?>>Swazi lilangeni (E) — SZL</option>
                                                <option value="THB" <?= $adminSettings['packageCurrency'] === 'THB' ? 'selected' : '' ?>>Thai baht (฿) — THB</option>
                                                <option value="TJS" <?= $adminSettings['packageCurrency'] === 'TJS' ? 'selected' : '' ?>>Tajikistani somoni (ЅМ) — TJS</option>
                                                <option value="TMT" <?= $adminSettings['packageCurrency'] === 'TMT' ? 'selected' : '' ?>>Turkmenistani manat (m) — TMT</option>
                                                <option value="TND" <?= $adminSettings['packageCurrency'] === 'TND' ? 'selected' : '' ?>>Tunisian dinar (د.ت) — TND</option>
                                                <option value="TOP" <?= $adminSettings['packageCurrency'] === 'TOP' ? 'selected' : '' ?>>Tongan paʻanga (T$) — TOP</option>
                                                <option value="TRY" <?= $adminSettings['packageCurrency'] === 'TRY' ? 'selected' : '' ?>>Turkish lira (₺) — TRY</option>
                                                <option value="TTD" <?= $adminSettings['packageCurrency'] === 'TTD' ? 'selected' : '' ?>>Trinidad and Tobago dollar (TT$) — TTD</option>
                                                <option value="TVD" <?= $adminSettings['packageCurrency'] === 'TVD' ? 'selected' : '' ?>>Tuvaluan dollar ($) — TVD</option>
                                                <option value="TWD" <?= $adminSettings['packageCurrency'] === 'TWD' ? 'selected' : '' ?>>New Taiwan dollar (NT$) — TWD</option>
                                                <option value="TZS" <?= $adminSettings['packageCurrency'] === 'TZS' ? 'selected' : '' ?>>Tanzanian shilling (Sh) — TZS</option>
                                                <option value="UAH" <?= $adminSettings['packageCurrency'] === 'UAH' ? 'selected' : '' ?>>Ukrainian hryvnia (₴) — UAH</option>
                                                <option value="UGX" <?= $adminSettings['packageCurrency'] === 'UGX' ? 'selected' : '' ?>>Ugandan shilling (USh) — UGX</option>
                                                <option value="USD" <?= $adminSettings['packageCurrency'] === 'USD' ? 'selected' : '' ?>>United States dollar ($) — USD</option>
                                                <option value="UYU" <?= $adminSettings['packageCurrency'] === 'UYU' ? 'selected' : '' ?>>Uruguayan peso ($U) — UYU</option>
                                                <option value="UZS" <?= $adminSettings['packageCurrency'] === 'UZS' ? 'selected' : '' ?>>Uzbekistani som (лв) — UZS</option>
                                                <option value="VES" <?= $adminSettings['packageCurrency'] === 'VES' ? 'selected' : '' ?>>Venezuelan bolívar soberano (Bs.S) — VES</option>
                                                <option value="VND" <?= $adminSettings['packageCurrency'] === 'VND' ? 'selected' : '' ?>>Vietnamese đồng (₫) — VND</option>
                                                <option value="VUV" <?= $adminSettings['packageCurrency'] === 'VUV' ? 'selected' : '' ?>>Vanuatu vatu (VT) — VUV</option>
                                                <option value="WST" <?= $adminSettings['packageCurrency'] === 'WST' ? 'selected' : '' ?>>Samoan tālā (WS$) — WST</option>
                                                <option value="XAF" <?= $adminSettings['packageCurrency'] === 'XAF' ? 'selected' : '' ?>>Central African CFA franc (Fr) — XAF</option>
                                                <option value="XCD" <?= $adminSettings['packageCurrency'] === 'XCD' ? 'selected' : '' ?>>East Caribbean dollar ($) — XCD</option>
                                                <option value="XOF" <?= $adminSettings['packageCurrency'] === 'XOF' ? 'selected' : '' ?>>West African CFA franc (Fr) — XOF</option>
                                                <option value="XPF" <?= $adminSettings['packageCurrency'] === 'XPF' ? 'selected' : '' ?>>CFP franc (Fr) — XPF</option>
                                                <option value="YER" <?= $adminSettings['packageCurrency'] === 'YER' ? 'selected' : '' ?>>Yemeni rial (﷼) — YER</option>
                                                <option value="ZAR" <?= $adminSettings['packageCurrency'] === 'ZAR' ? 'selected' : '' ?>>South African rand (R) — ZAR</option>
                                                <option value="ZMW" <?= $adminSettings['packageCurrency'] === 'ZMW' ? 'selected' : '' ?>>Zambian kwacha (ZK) — ZMW</option>
                                                <option value="ZWL" <?= $adminSettings['packageCurrency'] === 'ZWL' ? 'selected' : '' ?>>Zimbabwean dollar (Z$) — ZWL</option>
                                            </select>
                                            
                                            <div class="invalid-feedback">
                                                <?= lang('Pages.app_name_invalid_feedback') ?>
                                            </div>
                                        </div>
                                    </div><!--end col-->                                                       

                                </div><!--end row-->
                            </div>
                        </div>                            
                        
                        <!-- App Name -->
                        <div class="col-xl-6 mb-3">
                            <div class="card rounded shadow p-4 border-0 mb-3">
                                <label class="form-label" for="appName">
                                    <h5 class="mb-0">
                                        <?= lang('Pages.App_Name') ?> <span class="text-danger">*</span>
                                    </h5>
                                </label>
                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <div class="form-icon position-relative">
                                                <i data-feather="monitor" class="fea icon-sm icons"></i>
                                                <input name="appName" id="appName" type="text" class="form-control ps-5" value="<?= $adminSettings['appName'] ?? '' ?>" placeholder="<?= lang('Pages.preferred_app_name_placeholder') ?>" required>
                                                <div class="invalid-feedback">
                                                    <?= lang('Pages.app_name_invalid_feedback') ?>
                                                </div>                                                                        
                                            </div>
                                        </div>
                                    </div><!--end col-->                                                       

                                </div><!--end row-->
                            </div>
                        </div>

                        <!-- Company Name -->
                        <div class="col-xl-6 mb-3">
                            <div class="card rounded shadow p-4 border-0 mb-3">
                                <label class="form-label" for="companyName">
                                    <h5 class="mb-0">
                                        <?= lang('Pages.Company_Name') ?> <span class="text-danger">*</span>
                                    </h5>
                                </label>
                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <div class="form-icon position-relative">
                                                <i data-feather="monitor" class="fea icon-sm icons"></i>
                                                <input name="companyName" id="companyName" type="text" class="form-control ps-5" value="<?= $adminSettings['companyName'] ?? '' ?>" placeholder="<?= lang('Pages.the_company_name') ?>" required>
                                                <div class="invalid-feedback">
                                                    <?= lang('Pages.company_name_invalid_feedback') ?>
                                                </div>                                                                        
                                            </div>
                                        </div>
                                    </div><!--end col-->                                                       

                                </div><!--end row-->
                            </div>
                        </div>

                        <!-- Company Address -->
                        <div class="col-xl-6 mb-3">
                            <div class="card rounded shadow p-4 border-0 mb-3">
                                <label class="form-label" for="companyAddress">
                                    <h5 class="mb-0">
                                        <?= lang('Pages.Company_Address') ?> <span class="text-danger">*</span>
                                    </h5>
                                </label>
                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <div class="form-icon position-relative">
                                                <i data-feather="monitor" class="fea icon-sm icons"></i>
                                                <textarea name="companyAddress" id="companyAddress" rows="4" class="form-control ps-5" placeholder="<?= lang('Pages.the_company_address') ?>" required><?= $adminSettings['companyAddress'] ?? '' ?></textarea>
                                                <div class="invalid-feedback">
                                                    <?= lang('Pages.company_name_invalid_feedback') ?>
                                                </div>                                                                        
                                            </div>
                                        </div>
                                    </div><!--end col-->                                                       

                                </div><!--end row-->
                            </div>
                        </div>
                        
                        <!-- App Icon -->
                        <div class="col-xl-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">
                                <label class="form-label" for="appIcon">
                                    <h5 class="mb-0">
                                        <?= lang('Pages.Upload_App_Icon') ?> <span class="text-danger">*</span>
                                    </h5>
                                </label>

                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <p class="small text-info mb-0"><?= lang('Pages.max_dimension') ?> 256 x 256 px</p>
                                            <p class="small text-info"><?= lang('Pages.format') ?> *.jpg, *.jpeg, *.png</p>
                                            <p><img class="appIconPreview" src="<?= $adminSettings['appIcon'] ?>" height="64" alt="<?= $adminSettings['appName'] ?>"></p>

                                            <div class="form-icon position-relative input-group input-group-sm">
                                                <span class="input-group-text">
                                                    <a href="javascript:void(0)" class="restore-default-media text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Restore_default') ?>"><i class="mdi mdi-refresh"> </i> </a>                                                                            
                                                    <a href="javascript:void(0)" class="clear-media text-info px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Clear_input') ?>"><i class="mdi mdi-close"> </i> </a>                                                                            
                                                </span>
                                                &nbsp;
                                                <input class="form-control" name="appIcon" id="appIcon" type="file" accept=".jpg,.jpeg,.png">
                                                <div class="invalid-feedback">
                                                    <?= lang('Pages.image_format_requirement') ?>
                                                </div>                                                                     
                                            </div>
                                        </div>
                                    </div><!--end col-->                                                            

                                </div><!--end row-->
                            </div>
                        </div><!--end col-->        

                        <!-- Light App Logo -->
                        <div class="col-xl-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">
                                <h5 class="mb-0"><?= lang('Pages.Light_Mode_Logo') ?></h5>

                                <div class="row mt-4">

                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <p class="small text-info mb-0"><?= lang('Pages.max_height') ?> 60 px</p>
                                            <p class="small text-info"><?= lang('Pages.format') ?> *.jpg, *.jpeg, *.png</p>
                                            <p><img class="appLogo_lightPreview" src="<?= $adminSettings['appLogo_light'] ?>" height="60" alt="<?= $adminSettings['appName'] ?>"></p>

                                            <div class="form-icon position-relative input-group input-group-sm">
                                                <span class="input-group-text">
                                                    <a href="javascript:void(0)" class="restore-default-media text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Restore_default') ?>"><i class="mdi mdi-refresh"> </i> </a>
                                                    <a href="javascript:void(0)" class="clear-media text-info px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Clear_input') ?>"><i class="mdi mdi-close"> </i> </a> 
                                                </span>
                                                &nbsp;
                                                <input class="form-control" name="appLogo_light" id="appLogo_light" type="file" accept=".jpg,.jpeg,.png">
                                                <div class="invalid-feedback">
                                                    <?= lang('Pages.image_format_requirement') ?>
                                                </div>                                                                        
                                            </div>
                                            
                                        </div>
                                    </div><!--end col--> 															

                                </div><!--end row-->
                            </div>
                        </div>
                        
                        <!-- Dark App Logo -->
                        <div class="col-xl-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">
                                <h5 class="mb-0"><?= lang('Pages.Dark_Mode_Logo') ?></h5>

                                <div class="row mt-4">
                                    
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <p class="small text-info mb-0"><?= lang('Pages.max_height') ?> 60 px</p>
                                            <p class="small text-info"><?= lang('Pages.format') ?> *.jpg, *.jpeg, *.png</p>
                                            <p><img class="appLogo_darkPreview" src="<?= $adminSettings['appLogo_dark'] ?>" height="60" alt="<?= $adminSettings['appName'] ?>"></p>

                                            <div class="form-icon position-relative input-group input-group-sm">
                                                <span class="input-group-text">
                                                    <a href="javascript:void(0)" class="restore-default-media text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Restore_default') ?>"><i class="mdi mdi-refresh"> </i> </a>
                                                    <a href="javascript:void(0)" class="clear-media text-info px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Clear_input') ?>"><i class="mdi mdi-close"> </i> </a> 
                                                </span>
                                                &nbsp;
                                                <input class="form-control" name="appLogo_dark" id="appLogo_dark" type="file" accept=".jpg,.jpeg,.png">
                                                <div class="invalid-feedback">
                                                    <?= lang('Pages.image_format_requirement') ?>
                                                </div>                                                                        
                                            </div>
                                            
                                        </div>
                                    </div><!--end col-->    															

                                </div><!--end row-->
                            </div>
                        </div>
                        
                        <!-- Default theme -->
                        <div class="col-xl-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">
                                <h5 class="mb-0"><?= lang('Pages.Theme') ?></h5>
                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="defaultTheme"><?= lang('Pages.Default_Theme') ?> <a href="javascript:void(0)" class="delete-cookies-action text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Delete_cookies') ?>"><i class="mdi mdi-delete-circle-outline"> </i> </a></label>
                                            <div class="form-icon position-relative">
                                                <i data-feather="moon" class="fea icon-sm icons"></i>
                                                <select class="form-select form-control ps-5" id="defaultTheme" name="defaultTheme">
                                                    <option value=""><?= lang('Pages.Select_Option') ?></option>
                                                    <option value="light" <?= $adminSettings['defaultTheme'] === 'light' ? 'selected' : ''?> ><?= lang('Pages.Light_Mode') ?></option>
                                                    <option value="dark" <?= $adminSettings['defaultTheme'] === 'dark' ? 'selected' : ''?> ><?= lang('Pages.Dark_Mode') ?></option>
                                                    <option value="system" <?= $adminSettings['defaultTheme'] === 'system' ? 'selected' : ''?>><?= lang('Pages.Use_system_setting') ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div><!--end col-->                                                       

                                </div><!--end row-->
                            </div>                                                    
                        </div>
                        
                        <div class="col-xl-6 mb-3">
                            <!-- Date and Time -->
                            <div class="card border-0 rounded shadow p-4">
                                <h5 class="mb-0"><?= lang('Pages.Date_and_Time') ?></h5>
                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="defaultTimezone"><?= lang('Pages.Default_Timezone') ?> <span class="text-danger">*</span></label>
                                            <select class="form-select form-control ps-5" id="defaultTimezone" name="defaultTimezone" required="">
                                                <option value="">- <?= lang('Pages.Select_Timezone') ?> -</option>

                                                <?php 
                                                $timezone_references = json_decode(file_get_contents(ROOTPATH . 'public/assets/libs/timezone-codes.json'), true); 
                                                foreach($timezone_references as $continent => $places) { ?>
                                                    <optgroup label="<?= $continent ?>">
                                                        <?php foreach ($places as $place) { 
                                                            $value = $continent.'/'.$place;
                                                        ?>
                                                            <option value="<?= $value ?>" <?= $adminSettings['defaultTimezone'] === $value ? 'selected' : ''?> ><?= str_replace('_', ' ', $place) ?></option>
                                                        <?php } ?>
                                                    </optgroup>
                                                <?php } ?>

                                            </select>
                                            <div class="invalid-feedback">
                                                <?= lang('Pages.Default_Timezone_feedback') ?>
                                            </div> 
                                        </div>
                                    </div><!--end col-->                                                       

                                </div><!--end row-->
                            </div>                              
                        </div>

                        <!-- Locale -->
                        <div class="col-xl-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">
                                <h5 class="mb-0"><?= lang('Pages.Locale') ?></h5>
                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="defaultLocale"><?= lang('Pages.Default_Language') ?> <span class="text-danger">*</span></label>
                                            <div class="form-icon position-relative">
                                                <i data-feather="file-text" class="fea icon-sm icons"></i>
                                                <select class="form-select form-control ps-5" id="defaultLocale" name="defaultLocale" required="">
                                                    <option value="">- <?= lang('Pages.Select_Language') ?> -</option>
                                                    <?php foreach($languageDirectories as $languageDirectory) { ?>
                                                        <option value="<?= $languageDirectory ?>" <?= $adminSettings['defaultLocale'] === $languageDirectory ? 'selected' : ''?> ><?= $language_reference[$languageDirectory]['EnglishName'] ?></option>
                                                    <?php } ?>
                                                </select>
                                                <div class="invalid-feedback">
                                                    <?= lang('Pages.Select_Language_feedback') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!--end col-->                                                       

                                </div><!--end row-->
                            </div>                                 
                        </div>

                        <!-- Preload effect -->
                        <div class="col-xl-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">   
                                <h5 class="mb-0"><?= lang('Pages.Page_Preloading_Effects') ?></h5>

                                <div class="mt-4">
                                    <div class="d-flex justify-content-between pb-4">
                                        <label class="h6 mb-0" for="preloadEnabled"><?= lang('Pages.Page_Preloading_Effects_desc') ?></label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" <?= $adminSettings['preloadEnabled'] ? 'checked' : '' ?> id="preloadEnabled" name="preloadEnabled">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->
                        
                        <!-- reCAPTCHA settings for loginpage -->
                        <div class="col-xl-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">                                                                        
                                <div class="d-flex justify-content-between ">
                                    <label class="h5 mb-0" for="reCAPTCHA_enabled"><?= lang('Pages.Activate_reCAPTCHA_in_Login_Page') ?></label>
                                    <div class="form-check">                                                                  
                                        <input class="form-check-input" type="checkbox" <?= $adminSettings['reCAPTCHA_enabled'] ? 'checked' : '' ?> id="reCAPTCHA_enabled" name="reCAPTCHA_enabled">
                                    </div>
                                </div>

                                <label class="form-label mt-4" for="reCAPTCHA_Site_Key"><?= lang('Pages.reCAPTCHA_Site_Key') ?></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="key" class="fea icon-sm icons"></i>
                                    <input name="reCAPTCHA_Site_Key" id="reCAPTCHA_Site_Key" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.reCAPTCHA_Site_Key') ?>" value="<?= $adminSettings['reCAPTCHA_Site_Key'] ?? '' ?>">
                                </div>
                                
                                <label class="form-label mt-4" for="reCAPTCHA_Secret_Key"><?= lang('Pages.reCAPTCHA_Secret_Key') ?></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="key" class="fea icon-sm icons"></i>
                                    <input name="reCAPTCHA_Secret_Key" id="reCAPTCHA_Secret_Key" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.reCAPTCHA_Secret_Key') ?>" value="<?= $adminSettings['reCAPTCHA_Secret_Key'] ?? '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PWA Setup -->
                <div class="tab-pane" id="pwaSetup" role="tabpanel" aria-labelledby="pwa-setup">
                    <div class="row">
                        <!-- PWA App Names -->
                        <div class="col-lg-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">                                                                        
                                <div class="d-flex justify-content-between ">
                                    <label class="h5 mb-0" for="PWA_App_enabled"><?= lang('Pages.Enable_PWA_app') ?></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" <?= $myConfig['PWA_App_enabled'] ? 'checked' : '' ?> id="PWA_App_enabled" name="PWA_App_enabled">                                                                    
                                    </div>
                                </div>

                                <label class="form-label mt-4" for="PWA_App_name"><?= lang('Pages.PWA_App_name') ?></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="monitor" class="fea icon-sm icons"></i>
                                    <input name="PWA_App_name" id="PWA_App_name" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.PWA_App_name') ?>" value="<?= $myConfig['PWA_App_name'] ?? $myConfig['appName'] ?>">
                                </div>
                                
                                <label class="form-label mt-4" for="PWA_App_shortname"><?= lang('Pages.PWA_App_shortname') ?></label>
                                <small class="text-info mb-1"><?= lang('Pages.PWA_App_shortname_requirement') ?></small>
                                <div class="form-icon position-relative">
                                    <i data-feather="monitor" class="fea icon-sm icons"></i>
                                    <input name="PWA_App_shortname" id="PWA_App_shortname" type="text" class="form-control ps-5" placeholder="<?= lang('Pages.PWA_App_shortname') ?>" value="<?= $myConfig['PWA_App_shortname'] ?? 'ProdPanel' ?>">
                                </div>
                            </div>
                        </div><!--end col-->

                        <!-- PWA App Icon 192x192 -->
                        <div class="col-lg-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">
                                <label class="form-label" for="PWA_App_icon_192x192">
                                    <h5 class="mb-0">
                                        <?= lang('Pages.PWA_App_icon_192') ?>
                                    </h5>
                                </label>

                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <p class="small text-info mb-0"><?= lang('Pages.max_dimension') ?> 192 x 192 px</p>
                                            <p class="small text-info"><?= lang('Pages.format') ?> *.jpg, *.jpeg, *.png, *.svg, *.webp</p>
                                            <p><img class="PWA_App_icon_192x192Preview" src="<?= $myConfig['PWA_App_icon_192x192'] ? base_url('writable/uploads/app-custom-assets/' . $myConfig['PWA_App_icon_192x192']) : base_url('assets/images/meraf-PWA_App_icon_192x192.png') ?>" height="100" alt="<?= $myConfig['PWA_App_name'] ?>"></p>

                                            <div class="form-icon position-relative input-group input-group-sm">
                                                <span class="input-group-text">
                                                    <a href="javascript:void(0)" class="restore-default-media text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Restore_default') ?>"><i class="mdi mdi-refresh"> </i> </a>                                                                            
                                                    <a href="javascript:void(0)" class="clear-media text-info px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Clear_input') ?>"><i class="mdi mdi-close"> </i> </a>                                                                            
                                                </span>
                                                &nbsp;
                                                <input class="form-control" name="PWA_App_icon_192x192" id="PWA_App_icon_192x192" type="file" accept=".jpg,.jpeg,.png,.svg,.webp">
                                                <div class="invalid-feedback">
                                                    <?= lang('Pages.image_format_requirement_pwa_icon') ?>
                                                </div>                                                                     
                                            </div>
                                        </div>
                                    </div><!--end col-->
                                </div><!--end row-->
                            </div>
                        </div><!--end col-->

                        <!-- PWA App Icon 512x512 -->
                        <div class="col-lg-6 mb-3">
                            <div class="card border-0 rounded shadow p-4">
                                <label class="form-label" for="PWA_App_icon_512x512">
                                    <h5 class="mb-0">
                                        <?= lang('Pages.PWA_App_icon_512') ?>
                                    </h5>
                                </label>

                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <p class="small text-info mb-0"><?= lang('Pages.max_dimension') ?> 512 x 512 px</p>
                                            <p class="small text-info"><?= lang('Pages.format') ?> *.jpg, *.jpeg, *.png, *.svg, *.webp</p>
                                            <p><img class="PWA_App_icon_512x512Preview" src="<?= $myConfig['PWA_App_icon_512x512'] ? base_url('writable/uploads/app-custom-assets/' . $myConfig['PWA_App_icon_512x512']) : base_url('assets/images/meraf-PWA_App_icon_512x512.png') ?>" height="150" alt="<?= $myConfig['PWA_App_name'] ?>"></p>

                                            <div class="form-icon position-relative input-group input-group-sm">
                                                <span class="input-group-text">
                                                    <a href="javascript:void(0)" class="restore-default-media text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Restore_default') ?>"><i class="mdi mdi-refresh"> </i> </a>                                                                            
                                                    <a href="javascript:void(0)" class="clear-media text-info px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Clear_input') ?>"><i class="mdi mdi-close"> </i> </a>                                                                            
                                                </span>
                                                &nbsp;
                                                <input class="form-control" name="PWA_App_icon_512x512" id="PWA_App_icon_512x512" type="file" accept=".jpg,.jpeg,.png,.svg,.webp">
                                                <div class="invalid-feedback">
                                                    <?= lang('Pages.image_format_requirement_pwa_icon') ?>
                                                </div>                                                                     
                                            </div>
                                        </div>
                                    </div><!--end col-->
                                </div><!--end row-->
                            </div>
                        </div><!--end col-->

                    </div>
                </div>

                <!-- FCM Setup -->
                <div class="tab-pane" id="pushNotificationSetup" role="tabpanel" aria-labelledby="push-notification-setup">
                    <div class="row">
                        <!-- Modify email service settings -->
                        <div class="col-12 mb-3">
                            <div class="card border-0 rounded shadow p-4">
                                <div class="d-flex flex-column flex-md-row align-items-center">
                                    <h4 class="mb-0 me-md-4"><?= lang('Pages.Firebase_Cloud_Messaging') ?></h4>

                                    <div class="position-relative col-12 col-md-auto me-lg-3 mx-auto mb-3 mb-md-0 text-center mt-4 mt-md-0">
                                        <a href="javascript:void(0)" class="btn btn-soft-secondary btn-sm me-2" id="test-push-notification-btn">
                                            <i class="uil uil-bell"></i> <?= lang('Pages.Send_Test_Push_Notification') ?>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div id="result-container-wrapper" class="card border-0 rounded shadow p-4 mt-3 position-relative" style="display: none;">
                                <!-- Close button -->
                                <button type="button" class="btn btn-icon btn-close-dark position-absolute top-0 end-0 m-3" id="hide-result-container"><i class="uil uil-times fs-4 text-dark"></i></button>

                                <div class="col-12 mt-4 mt-md-0">
                                    <h5 class="mb-3"><?= lang('Pages.Result') ?></h5>
                                    <div id="result-container" class="result-container border rounded p-3 bg-light">
                                        <p class="text-muted"><?= lang('Pages.Results_will_appear_here') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- FCM Credentials -->
                        <div class="col-lg-8 mb-3">
                            <div class="card border-0 rounded shadow p-4">                                                                        
                                <div class="d-flex justify-content-between ">
                                    <label class="h5 mb-0" for="push_notification_feature_enabled"><?= lang('Pages.Enable_push_notification') ?></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" <?= $myConfig['push_notification_feature_enabled'] ? 'checked' : '' ?> id="push_notification_feature_enabled" name="push_notification_feature_enabled">                                                                    
                                    </div>
                                </div>

                                <h5 class="mt-4">
                                    <?= lang('Pages.Firebase_Cloud_Messaging_Credentials') ?> 
                                    <a
                                        href="https://firebase.google.com/docs/cloud-messaging/js/receive#web"
                                        target="_blank"
                                        class="text-info"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-original-title="<?= lang('Pages.Learn_More') ?>">
                                            <i class="h6 mdi mdi-account-question"></i>
                                    </a> :
                                </h5>

                                <label class="form-label mt-2" for="fcm_apiKey"><?= lang('Pages.fcm_apiKey') ?></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="code" class="fea icon-sm icons"></i>
                                    <input name="fcm_apiKey" id="fcm_apiKey" type="text" class="fcm-field form-control ps-5" placeholder="<?= lang('Pages.fcm_apiKey') ?>" value="<?= $myConfig['fcm_apiKey'] ?? $myConfig['fcm_apiKey'] ?>">
                                </div>
                                
                                <label class="form-label mt-4" for="fcm_authDomain"><?= lang('Pages.fcm_authDomain') ?></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="code" class="fea icon-sm icons"></i>
                                    <input name="fcm_authDomain" id="fcm_authDomain" type="text" class="fcm-field form-control ps-5" placeholder="<?= lang('Pages.fcm_authDomain') ?>" value="<?= $myConfig['fcm_authDomain'] ?? '' ?>">
                                </div>

                                <label class="form-label mt-4" for="fcm_projectId"><?= lang('Pages.fcm_projectId') ?></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="code" class="fea icon-sm icons"></i>
                                    <input name="fcm_projectId" id="fcm_projectId" type="text" class="fcm-field form-control ps-5" placeholder="<?= lang('Pages.fcm_projectId') ?>" value="<?= $myConfig['fcm_projectId'] ?? '' ?>">
                                </div>

                                <label class="form-label mt-4" for="fcm_storageBucket"><?= lang('Pages.fcm_storageBucket') ?></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="code" class="fea icon-sm icons"></i>
                                    <input name="fcm_storageBucket" id="fcm_storageBucket" type="text" class="fcm-field form-control ps-5" placeholder="<?= lang('Pages.fcm_storageBucket') ?>" value="<?= $myConfig['fcm_storageBucket'] ?? '' ?>">
                                </div>

                                <label class="form-label mt-4" for="fcm_messagingSenderId"><?= lang('Pages.fcm_messagingSenderId') ?></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="code" class="fea icon-sm icons"></i>
                                    <input name="fcm_messagingSenderId" id="fcm_messagingSenderId" type="text" class="fcm-field form-control ps-5" placeholder="<?= lang('Pages.fcm_messagingSenderId') ?>" value="<?= $myConfig['fcm_messagingSenderId'] ?? '' ?>">
                                </div>

                                <label class="form-label mt-4" for="fcm_appId"><?= lang('Pages.fcm_appId') ?></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="code" class="fea icon-sm icons"></i>
                                    <input name="fcm_appId" id="fcm_appId" type="text" class="fcm-field form-control ps-5" placeholder="<?= lang('Pages.fcm_appId') ?>" value="<?= $myConfig['fcm_appId'] ?? '' ?>">
                                </div>

                                <label class="form-label mt-4" for="fcm_measurementId"><?= lang('Pages.fcm_measurementId') ?></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="code" class="fea icon-sm icons"></i>
                                    <input name="fcm_measurementId" id="fcm_measurementId" type="text" class="fcm-field form-control ps-5" placeholder="<?= lang('Pages.fcm_measurementId') ?>" value="<?= $myConfig['fcm_measurementId'] ?? '' ?>">
                                </div>
                                
                                <h5 class="mt-4">
                                    <?= lang('Pages.fcm_vapidKey_heading') ?> 
                                    <a
                                        href="https://firebase.google.com/docs/cloud-messaging/js/client#configure_web_credentials_in_your_app"
                                        target="_blank"
                                        class="text-info"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-original-title="<?= lang('Pages.Learn_More') ?>">
                                            <i class="h6 mdi mdi-account-question"></i>
                                    </a> :
                                </h5>

                                <label class="form-label mt-2 border-top" for="fcm_vapidKey"><?= lang('Pages.fcm_vapidKey') ?></label>
                                <div class="form-icon position-relative">
                                    <i data-feather="code" class="fea icon-sm icons"></i>
                                    <input name="fcm_vapidKey" id="fcm_vapidKey" type="text" class="fcm-field form-control ps-5" placeholder="<?= lang('Pages.fcm_vapidKey') ?>" value="<?= $myConfig['fcm_vapidKey'] ?? '' ?>">
                                </div>
                            </div>
                        </div><!--end col-->

                        <div class="col-lg-4 mb-3">
                            <!-- FCM Private Key File -->
                            <form class="mb-3" novalidate enctype="multipart/form-data" action="javascript:void(0)" id="upload-private-key-file-form">
                                <div class="card border-0 rounded shadow p-4 mb-3">
                                    <label class="form-label" for="fcm_private_key_file">
                                        <h5 class="mb-0">
                                            <?= lang('Pages.Private_Key_File') ?> 
                                            <a
                                                href="https://firebase.google.com/docs/cloud-messaging/auth-server#provide-credentials-manually"
                                                target="_blank"
                                                class="text-info"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="bottom"
                                                data-bs-original-title="<?= lang('Pages.Learn_More') ?>">
                                                    <i class="h6 mdi mdi-account-question"></i>
                                            </a>
                                        </h5>
                                    </label>

                                    <div class="row mt-4">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <p class="small text-info"><?= lang('Pages.private_key_file_note') ?></p>

                                                <div class="form-icon position-relative input-group input-group-sm">
                                                    <span class="input-group-text">
                                                        <a href="javascript:void(0)" class="delete-saved-file text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Delete_Saved_File') ?>"><i class="mdi mdi-trash-can"></i></a>
                                                        <a id="inputClearPrivateKeyFileIcon" href="javascript:void(0)" class="clear-media text-info px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Clear_input') ?>" <?= $myConfig['fcm_private_key_file'] ? 'style="display:none;"' : 'style="display:d-block;"' ?>><i class="mdi mdi-close"></i></a>
                                                        <a id="inputSavedPrivateKeyFileIcon" href="javascript:void(0)" class="text-success px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.existing_private_key_file') ?>" <?= !$myConfig['fcm_private_key_file'] ? 'style="display:none;"' : 'style="display:d-block;"' ?>><i class="mdi mdi-check-circle-outline"></i></a>
                                                    </span>
                                                    &nbsp;
                                                    <input class="form-control" name="fcm_private_key_file" id="fcm_private_key_file" type="file" accept=".json" <?= $myConfig['fcm_private_key_file'] ? 'style="display:none;"' : 'style="display:d-block;"' ?>>
                                                    <input id="inputSavedPrivateKeyFile" type="text" class="form-control" value="<?= lang('Pages.Private_Key_File_Saved') ?>" <?= !$myConfig['fcm_private_key_file'] ? 'style="display:none;"' : 'style="display:d-block;"' ?> disabled>
                                                    <div class="invalid-feedback">
                                                        <?= lang('Pages.private_key_file_format') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 text-center">
                                            <button class="mx-auto btn btn-primary" id="upload-private-key-file-submit" <?= $myConfig['fcm_private_key_file'] ? 'disabled' : '' ?> ><i class="uil uil-arrow-circle-up"></i> <?= lang('Pages.Upload_Private_Key_File') ?></button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Notification badge 96x96px -->
                            <form class="mb-3" novalidate enctype="multipart/form-data" action="javascript:void(0)" id="upload-notification-badge-form">
                                <div class="card border-0 rounded shadow p-4 mb-3">
                                    <label class="form-label" for="push_notification_badge">
                                        <h5 class="mb-0">
                                            <?= lang('Pages.Notification_Badge') ?> 
                                            <a
                                                href="https://romannurik.github.io/AndroidAssetStudio/icons-notification.html#source.type=clipart&source.clipart=account_balance_wallet&source.space.trim=1&source.space.pad=0&name=ic_stat_onesignal_default"
                                                target="_blank"
                                                class="text-info"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="bottom"
                                                data-bs-original-title="<?= lang('Pages.Icon_Generator') ?>">
                                                    <i class="h6 mdi mdi-tools"></i>
                                            </a>
                                        </h5>
                                    </label>

                                    <div class="row mt-4">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <p class="small text-info mb-0"><?= lang('Pages.max_dimension') ?> 96 x 96 px</p>
                                                <p class="small text-info"><?= lang('Pages.format') ?> *.jpg, *.jpeg, *.png, *.svg, *.webp</p>
                                                <p class="small text-info"><?= lang('Pages.notification_badge_note') ?></p>

                                                <p>
                                                    <img class="push_notification_badgePreview" src="<?= $myConfig['push_notification_badge'] ?? base_url('assets/images/meraf-push_notification_badge.png') ?>" height="96" alt="<?= esc($myConfig['PWA_App_name']) ?>">
                                                </p>

                                                <div class="form-icon position-relative input-group input-group-sm">
                                                    <span class="input-group-text">
                                                        <a href="javascript:void(0)" class="restore-default-media text-danger px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Restore_default') ?>"><i class="mdi mdi-refresh"></i></a>
                                                        <a href="javascript:void(0)" class="clear-media text-info px-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="<?= lang('Pages.Clear_input') ?>"><i class="mdi mdi-close"></i></a>
                                                    </span>
                                                    &nbsp;

                                                    <input class="form-control" name="push_notification_badge" id="push_notification_badge" type="file" accept=".jpg,.jpeg,.png,.svg,.webp">
                                                    <div class="invalid-feedback">
                                                        <?= lang('Pages.image_format_requirement_pwa_icon') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 text-center">
                                            <button class="mx-auto btn btn-primary" id="upload-notification-badge-submit"><i class="uil uil-arrow-circle-up"></i> <?= lang('Pages.Upload_Notification_Badge') ?></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
    <!-- Cache Settings Modal -->
    <div class="modal fade" id="cacheSettingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title"><?= lang('Pages.Cache_Settings_Configuration') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body p-0">
                    <form class="p-4" id="cache-settings-form" novalidate>

                        <!-- Memcached Settings -->
                        <div id="memcachedSettings" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label" for="memcached_host"><?= lang('Pages.Host') ?></label>
                                <div class="form-icon position-relative">
                                    <i class="uil uil-server-alt position-absolute top-50 translate-middle-y ms-3"></i>
                                    <input type="text" class="form-control ps-5" id="memcached_host" name="memcached_host" value="<?= $myConfig['memcached_host'] ?? '127.0.0.1' ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="memcached_port"><?= lang('Pages.Port') ?></label>
                                <div class="form-icon position-relative">
                                    <i class="uil uil-plug position-absolute top-50 translate-middle-y ms-3"></i>
                                    <input type="number" class="form-control ps-5" id="memcached_port" name="memcached_port" value="<?= $myConfig['memcached_port'] ?? '11211' ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="memcached_weight"><?= lang('Pages.Weight') ?></label>
                                <div class="form-icon position-relative">
                                    <i class="uil uil-balance-scale position-absolute top-50 translate-middle-y ms-3"></i>
                                    <input type="number" class="form-control ps-5" id="memcached_weight" name="memcached_weight" value="<?= $myConfig['memcached_weight'] ?? '1' ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="memcached_raw"><?= lang('Pages.Raw') ?></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="memcached_raw" name="memcached_raw" <?= isset($myConfig['memcached_raw']) && $myConfig['memcached_raw'] ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </div>

                        <!-- Redis Settings -->
                        <div id="redisSettings" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label" for="redis_host"><?= lang('Pages.Host') ?></label>
                                <div class="form-icon position-relative">
                                    <i class="uil uil-server position-absolute top-50 translate-middle-y ms-3"></i>
                                    <input type="text" class="form-control ps-5" id="redis_host" name="redis_host" value="<?= $myConfig['redis_host'] ?? '127.0.0.1' ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="redis_port"><?= lang('Pages.Port') ?></label>
                                <div class="form-icon position-relative">
                                    <i class="uil uil-plug position-absolute top-50 translate-middle-y ms-3"></i>
                                    <input type="number" class="form-control ps-5" id="redis_port" name="redis_port" value="<?= $myConfig['redis_port'] ?? '6379' ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="redis_password"><?= lang('Pages.Password') ?></label>
                                <div class="form-icon position-relative">
                                    <i class="uil uil-lock position-absolute top-50 translate-middle-y ms-3"></i>
                                    <input type="password" class="form-control ps-5" id="redis_password" name="redis_password" value="<?= $myConfig['redis_password'] ?? '' ?>">
                                </div>
                                <small class="text-muted"><?= lang('Pages.Leave_blank_if_no_password') ?></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="redis_timeout"><?= lang('Pages.Timeout') ?></label>
                                <div class="form-icon position-relative">
                                    <i class="uil uil-clock position-absolute top-50 translate-middle-y ms-3"></i>
                                    <input type="number" class="form-control ps-5" id="redis_timeout" name="redis_timeout" value="<?= $myConfig['redis_timeout'] ?? '0' ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="redis_database"><?= lang('Pages.Database') ?></label>
                                <div class="form-icon position-relative">
                                    <i class="uil uil-database position-absolute top-50 translate-middle-y ms-3"></i>
                                    <input type="number" class="form-control ps-5" id="redis_database" name="redis_database" value="<?= $myConfig['redis_database'] ?? '0' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 text-center mt-4">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="saveCacheSettings"><i class="uil uil-save"></i> <?= lang('Pages.Save_Settings') ?></button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cache Handler Test Result -->
     <div class="modal fade" id="cacheTestResultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded shadow border-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title"><?= lang('Pages.Test_Connection_Result') ?></h5>
                    <button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal"><i class="uil uil-times fs-4 text-dark"></i></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Result container for cache connection test -->
                    <div id="cache-result-container-wrapper" class="p-4" style="display: none;">
                        <div class="col-12 mt-4 mt-md-0">
                            <div id="cache-result-container" class="result-container border rounded p-3 bg-light">
                                <p class="text-muted"><?= lang('Pages.Results_will_appear_here') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() //End section('modals')?>

<?= $this->section('scripts') ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#packageCurrency').select2();
            $('#defaultTimezone').select2();

            function disableUploadPrivateKeyForm(disabled) {
                
                const fileInput = $('#fcm_private_key_file');
                const submitButton = $('#upload-private-key-file-submit');
                const checkboxEnabled = $('#push_notification_feature_enabled');
                const inputSavedPrivateKeyFile = $('#inputSavedPrivateKeyFile');
                const inputSavedPrivateKeyFileIcon = $('#inputSavedPrivateKeyFileIcon');
                const inputClearPrivateKeyFileIcon = $('#inputClearPrivateKeyFileIcon');
                
                if(disabled) {
                  fileInput.prop('disabled', true);
                  fileInput.hide();
                  submitButton.prop('disabled', true); 
                  inputSavedPrivateKeyFile.show();
                  inputSavedPrivateKeyFileIcon.show();
                  inputClearPrivateKeyFileIcon.hide();
                }
                else {
                  fileInput.prop('disabled', false);
                  fileInput.show();
                  submitButton.prop('disabled', false);
                  checkboxEnabled.prop('checked', false);
                  inputSavedPrivateKeyFile.hide();
                  inputSavedPrivateKeyFileIcon.hide();
                  inputClearPrivateKeyFileIcon.show();
                }
            }

            $('#hide-result-container').on('click', function () {
                $('#result-container-wrapper').slideUp();
            });

            /**
             * Handle Cache handler settings modal
             */
            function updateCacheTestButtonVisibility() {
                const selectedHandler = $('#cacheHandler').val();
                if (selectedHandler === 'memcached' || selectedHandler === 'redis' || selectedHandler === 'predis') {
                    $('#cache-connection-test-container').show();
                } else {
                    $('#cache-connection-test-container').hide();
                }
            }

            // Then, set up the change event handler for the cache handler dropdown
            $('#cacheHandler').on('change', function() {
                const selectedHandler = $(this).val();
                updateCacheTestButtonVisibility();
                
                // Only show the modal if this is a user-initiated change (not our trigger)
                if ((selectedHandler === 'memcached' || selectedHandler === 'redis') && !window.initialCacheHandlerLoad) {
                    // Hide all settings panels first
                    $('#memcachedSettings, #redisSettings').hide();
                    
                    // Show the appropriate settings panel
                    if (selectedHandler === 'memcached') {
                        $('#memcachedSettings').show();
                        $('#cacheSettingsModalLabel').text('<?= lang('Pages.Memcached_Settings') ?>');
                    } else if (selectedHandler === 'redis') {
                        $('#redisSettings').show();
                        $('#cacheSettingsModalLabel').text('<?= lang('Pages.Redis_Settings') ?>');
                    }
                    
                    // Show the modal
                    $('#cacheSettingsModal').modal('show');
                }
            });

            // Set a flag to prevent modal from showing on initial load
            window.initialCacheHandlerLoad = true;

            // Trigger the change event to set initial visibility
            updateCacheTestButtonVisibility();

            // Reset the flag after initial load
            setTimeout(function() {
                window.initialCacheHandlerLoad = false;
            }, 500);

            /**
             * Handle Save cache settings
             */
            $('#saveCacheSettings').on('click', function(e) {
                e.preventDefault();
                const selectedHandler = $('#cacheHandler').val();
                
                // Save the settings based on the selected handler
                if (selectedHandler === 'memcached') {
                    // Save memcached settings to hidden inputs that will be submitted with the form
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'memcached_host',
                        value: $('#memcached_host').val()
                    }).appendTo('#global-settings-form');
                    
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'memcached_port',
                        value: $('#memcached_port').val()
                    }).appendTo('#global-settings-form');
                    
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'memcached_weight',
                        value: $('#memcached_weight').val()
                    }).appendTo('#global-settings-form');
                    
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'memcached_raw',
                        value: $('#memcached_raw').is(':checked') ? '1' : '0'
                    }).appendTo('#global-settings-form');
                } else if (selectedHandler === 'redis') {
                    // Save redis settings to hidden inputs that will be submitted with the form
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'redis_host',
                        value: $('#redis_host').val()
                    }).appendTo('#global-settings-form');
                    
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'redis_port',
                        value: $('#redis_port').val()
                    }).appendTo('#global-settings-form');
                    
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'redis_password',
                        value: $('#redis_password').val()
                    }).appendTo('#global-settings-form');
                    
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'redis_timeout',
                        value: $('#redis_timeout').val()
                    }).appendTo('#global-settings-form');
                    
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'redis_database',
                        value: $('#redis_database').val()
                    }).appendTo('#global-settings-form');
                }
                
                // Close the modal
                $('#cacheSettingsModal').modal('hide');
                
                // Show a success message
                showToast('success', '<?= lang('Pages.Cache_Settings_Saved') ?>');
            });

            /*****************************
            // Validate the reCAPTCHA form
            *****************************/
            function toggleReCAPTCHA() {
                var siteKey = $('#reCAPTCHA_Site_Key').val().trim();
                var secretKey = $('#reCAPTCHA_Secret_Key').val().trim();

                if (siteKey === "" || secretKey === "") {
                    $('#reCAPTCHA_enabled').prop('checked', false).prop('disabled', true);
                } else {
                    $('#reCAPTCHA_enabled').prop('disabled', false);
                }
            }

            // Initial check on page load
            toggleReCAPTCHA();

            // Trigger the check on input changes
            $('#reCAPTCHA_Site_Key, #reCAPTCHA_Secret_Key').on('input', function() {
                toggleReCAPTCHA();
            });
                            
            /***************************
             * Handle clear server cache
             ***************************/
            $('#clear-server-cache-btn').on('click', function (e) {

                var submitButton = $(this);
                var confirmDelete = confirm("<?= lang('Pages.confirmation_clear_server_cache') ?>");

                if (confirmDelete) {
                    // Proceed with AJAX request if user confirms
                    $.ajax({
                        url: '<?= base_url('admin-options/clear-server-cache') ?>',
                        method: 'GET',
                        success: function (response) {
                            let toastType = 'info';

                            if (response.success === true && response.status === 1) {
                                toastType = 'success';
							} else if (response.success === false && response.status === 0) {
								toastType = 'info';
                            } else {
                                toastType = 'danger';
                            }

                            showToast(toastType, response.msg);
                        },
                        error: function (xhr, status, error) {
                            // Show error toast
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        },
                        complete: function () {
                            disableLoadingEffect(submitButton);
                        }
                    });
                }
            });

            /******************************
            // Handle the app settings save
            ******************************/
            $('#global-settings-submit').on('click', function (e) {
                e.preventDefault();

                const formElement = $('#global-settings-form');
                const submitButtonElement = $(this);
                const defaultTheme = $('#defaultTheme');

                // Enable button loading effect
                enableLoadingEffect(submitButtonElement);

                // Remove existing 'is-invalid' classes
                formElement.find('.is-invalid').removeClass('is-invalid').end().find('.is-valid').removeClass('is-valid');

                // Validation logic
                let isValid = true;
                let validationErrors = [];

                // Validate <select> elements
                const validateSelectInputs = () => {
                    formElement.find('select').not(defaultTheme).each(function () {
                        const selectInput = $(this);
                        const inputWithError = selectInput.attr('id');
                        let errorPlaceholder = selectInput.parent().find('label').text().replace('*', '');

                        if (selectInput.val() === '') {
                            selectInput.addClass('is-invalid');
                            isValid = false;
                            showToast('danger', '<?= lang('Pages.required_to_select_option') ?>' + errorPlaceholder.trim());
                        } else {
                            selectInput.addClass('is-valid');
                        }                                

                    });
                };

                // Validate FCM Credentials
                const validateFcmCredentials = () => {
                    const fcmEnabled = $('#push_notification_feature_enabled').is(':checked');
                    if (fcmEnabled) {
                        // Missing # in selectors and extra || at the end of conditions
                        if (
                            $('#fcm_apiKey').val().trim() === '' ||
                            $('#fcm_authDomain').val().trim() === '' ||
                            $('#fcm_projectId').val().trim() === '' ||
                            $('#fcm_storageBucket').val().trim() === '' ||
                            $('#fcm_messagingSenderId').val().trim() === '' ||
                            $('#fcm_appId').val().trim() === '' ||
                            $('#fcm_measurementId').val().trim() === '' ||
                            $('#fcm_vapidKey').val().trim() === ''  // Removed extra || here
                        ) {
                            // fcmEnabled is a string value, not a jQuery object
                            // We should add is-invalid class to each empty field instead
                            $('.fcm-field').removeClass('is-invalid');
                            
                            if ($('#fcm_apiKey').val().trim() === '') $('#fcm_apiKey').addClass('is-invalid');
                            if ($('#fcm_authDomain').val().trim() === '') $('#fcm_authDomain').addClass('is-invalid');
                            if ($('#fcm_projectId').val().trim() === '') $('#fcm_projectId').addClass('is-invalid');
                            if ($('#fcm_storageBucket').val().trim() === '') $('#fcm_storageBucket').addClass('is-invalid');
                            if ($('#fcm_messagingSenderId').val().trim() === '') $('#fcm_messagingSenderId').addClass('is-invalid');
                            if ($('#fcm_appId').val().trim() === '') $('#fcm_appId').addClass('is-invalid');
                            if ($('#fcm_measurementId').val().trim() === '') $('#fcm_measurementId').addClass('is-invalid');
                            if ($('#fcm_vapidKey').val().trim() === '') $('#fcm_vapidKey').addClass('is-invalid');
                            
                            showToast('danger', '<?= lang('Pages.fcm_field_required_when_enabled') ?>');
                            // Disable loading effect after error toast
                            disableLoadingEffect(submitButtonElement);
                        }
                    }
                };

                // Perform validations
                validateFcmCredentials();
                validateSelectInputs();

                if (!isValid) {
                    showToast('danger', '<?= lang('Notifications.correct_the_highlighted_errors') ?>');
                    disableLoadingEffect(submitButtonElement);
                    return;
                }

                // Check if there are any elements with 'is-invalid' class
                if (formElement.find('.is-invalid').length === 0) {

                    // Form data is valid, proceed with further processing
                    var data = new FormData(formElement[0]);

                    // Append additional data to the FormData object
                    // General
                    data.append('appName', $('#appName').val());
                    data.append('defaultTimezone', $('#defaultTimezone').val());
                    data.append('defaultLocale', $('#defaultLocale').val());
                    data.append('defaultTheme', $('#defaultTheme').val());
                    data.append('reCAPTCHA_Site_Key', $('#reCAPTCHA_Site_Key').val());
                    data.append('reCAPTCHA_Secret_Key', $('#reCAPTCHA_Secret_Key').val());

                    // Check if appLogo file is selected
                    const appLogo_lightInput = $('#appLogo_light')[0];
                    if (appLogo_lightInput.files.length > 0) {
                        data.append('appLogo_light', appLogo_lightInput.files[0]);
                    }

                    // Check if appLogo file is selected
                    const appLogo_darkInput = $('#appLogo_dark')[0];
                    if (appLogo_darkInput.files.length > 0) {
                        data.append('appLogo_dark', appLogo_darkInput.files[0]);
                    }                        

                    // Check if appIcon file is selected
                    const appIconInput = $('#appIcon')[0];
                    if (appIconInput.files.length > 0) {
                        data.append('appIcon', appIconInput.files[0]);
                    }

                    // Use AJAX to submit the form data
                    $.ajax({
                        url: '<?= base_url('admin-options/global-settings/save') ?>',
                        method: 'POST',
                        processData: false,
                        contentType: false,
                        data: data,
                        success: function (response) {
                            var alertsArray = []; // Array to store alert HTML

                            if (response.cacheHandler && response.cacheHandler.success === false) {
                                $('#cacheHandler').addClass('is-invalid');
                                toastType = 'danger';

								showToast(toastType, response.cacheHandler.msg);
                            }

                            // Handle inputs response
                            if (response.inputs) {
                                if (response.inputs.success) {
                                    showToast('success', response.inputs.msg);
                                } else {
                                    if (typeof response.inputs.msg === 'object') {
                                        // Display each error message in a separate alert
                                        Object.keys(response.inputs.msg).forEach(function (key) {
                                            showToast('danger', response.inputs.msg[key]);
                                            const inputElement = $('#' + key);
                                            if (inputElement.length) {
                                                inputElement.addClass('is-invalid');
                                            }
                                        });
                                    } else {
                                        // Display as a single alert
                                        showToast('danger', response.inputs.msg);
                                    }
                                }
                            }

                            // Handle appLogo response
                            if (response.appLogo_light) {
                                handleFileUploadResponse(response.appLogo_light)
                            }

                            if (response.appLogo_dark) {
                                handleFileUploadResponse(response.appLogo_dark)
                            }                                

                            if (response.appIcon) {
                                handleFileUploadResponse(response.appIcon)
                            }

                            if (response.PWA_App_icon_192x192) {
                                handleFileUploadResponse(response.PWA_App_icon_192x192)
                            }

                            if (response.PWA_App_icon_512x512) {
                                handleFileUploadResponse(response.PWA_App_icon_512x512)
                            }
                        },
                        error: function (xhr, status, error) {
                            // Show error toast
		                    showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                        },
                        complete: function () {
                            disableLoadingEffect(submitButtonElement);
                        }
                    });

                    function handleFileUploadResponse(fileResponse) {
                        if (fileResponse && fileResponse.success) {
                            if(fileResponse) {
                                var fileName = fileResponse.fileName;
                                var type = fileResponse.updateElement;
                                var newSrc = '<?= base_url('writable/uploads/app-custom-assets/') ?>' + fileName;
                                $('.' + type +'Preview').attr('src', newSrc);
                                $('#' + fileResponse.updateElement).val('');
                            }
                            showToast('success', fileResponse.msg);
                        } else if (fileResponse) {
							if (typeof fileResponse.msg === 'object') {
								// Display each error message in a separate alert
								Object.keys(fileResponse.msg).forEach(function (key) {
									showToast('danger', fileResponse.msg[key]);
									const inputElement = $('#' + key);
									if (inputElement.length) {
										inputElement.addClass('is-invalid');
									}
								});
							} else {
								// Display as a single alert
								showToast('danger', fileResponse.msg);
							}

                            // Add is-invalid class to corresponding element based on key
                            var key = fileResponse.errorElement;
                            $('#' + key).addClass('is-invalid');
                        }
                    }
                }
            });

            /**************************************
            // Handle the Upload Notification Badge
            **************************************/
            $('#upload-notification-badge-submit').on('click', function (e) {
                e.preventDefault();
            
                // Get the file input directly
                const fileInput = document.getElementById('push_notification_badge');
                const submitButton = $(this);
                
                enableLoadingEffect(submitButton);
                
                // Check if a file was selected
                if (fileInput.files.length === 0) {
                    $(fileInput).addClass('is-invalid');
                    disableLoadingEffect(submitButton);
                    return;
                }
                
                // Create a new FormData object
                const formData = new FormData();
                
                // Add the file to the FormData object
                formData.append('push_notification_badge', fileInput.files[0]);
                
                // Check file type
                const fileName = fileInput.value.split('\\').pop();
                const fileExtension = fileName.split('.').pop().toLowerCase();
                if (!['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(fileExtension)) {
                    $(fileInput).addClass('is-invalid');
                    disableLoadingEffect(submitButton);
                    return;
                }
                
                $.ajax({
                    url: '<?= base_url('admin-options/global-settings/upload-notification-badge') ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $(fileInput).val('');
                        $(fileInput).removeClass('is-invalid');
                        
                        const toastType = response.success ? 'success' : 'danger';
                        if (response.success) {
                            $('.push_notification_badgePreview').attr('src', response.newNotificationBadge);
                        }
                        
                        showToast(toastType, response.msg);
                    },
                    error: function (xhr, status) {
                        showToast('danger', '' + status.toUpperCase() + ' ' + xhr.status);
                    },
                    complete: function () {
                        disableLoadingEffect(submitButton);
                    }
                });
            });

            /************************************
            // Handle the Upload Private Key File
            ************************************/
            $('#upload-private-key-file-submit').on('click', function (e) {
                e.preventDefault();

                const fileInput = $('#fcm_private_key_file');
                const submitButton = $(this);
                const file = fileInput[0].files[0];

                enableLoadingEffect(submitButton);

                // Validate file presence
                if (!file) {
                    fileInput.addClass('is-invalid');
                    disableLoadingEffect(submitButton);
                    return;
                }

                // Validate file extension
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (fileExtension !== 'json') {
                    fileInput.addClass('is-invalid');
                    disableLoadingEffect(submitButton);
                    return;
                }

                const formData = new FormData();
                formData.append('fcm_private_key_file', file);

                $.ajax({
                    url: '<?= base_url('admin-options/global-settings/upload-private-key-file') ?>',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        fileInput.val('').removeClass('is-invalid');

                        if (response.success) {
                            disableUploadPrivateKeyForm(response.success);
                        }
                        else {
                            disableUploadPrivateKeyForm(false);
                        }

                        showToast(response.success ? 'success' : 'danger', response.msg);
                    },
                    error: function (xhr, status) {
                        showToast('danger', `${status.toUpperCase()} ${xhr.status}`);
                    },
                    complete: function () {
                        disableLoadingEffect(submitButton);
                    }
                });
            });

            $('.delete-saved-file').on('click', function (e) {
                e.preventDefault();

                const confirmed = confirm('<?= lang('Pages.alert_delete_private_key') ?>');

                if (!confirmed) return;

                const submitButton = $(this);
                submitButton.prop('disabled', true);
                enableLoadingEffect(submitButton); // optional

                $.ajax({
                    url: '<?= base_url('admin-options/global-settings/delete-private-key-file') ?>',
                    method: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        showToast(response.success ? 'success' : 'danger', response.msg);

                        if (response.success) {
                            disableUploadPrivateKeyForm(false);
                        }
                        else {
                            disableUploadPrivateKeyForm(true);
                        }
                    },
                    error: function (xhr, status) {
                        showToast('danger', `${status.toUpperCase()} ${xhr.status}`);
                    },
                    complete: function () {
                        disableLoadingEffect(submitButton); // optional
                        submitButton.prop('disabled', false);
                    }
                });
            });

            $('#test-push-notification-btn').on('click', function() {
                const button = $(this);
                const resultContainerWrapper = $('#result-container-wrapper');
                const resultContainer = $('#result-container');
                
                // Show the result area
                resultContainerWrapper.slideDown();

                // Disable button and show loading state
                button.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin mb-0 align-middle"></i> <?= lang('Pages.Sending') ?>');
                
                // Send AJAX request
                $.ajax({
                    url: '<?= base_url('admin-options/global-settings/send-test-push-notification') ?>',
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        // Format the response as JSON
                        const formattedResponse = JSON.stringify(response, null, 2);
                        
                        // Display the result
                        resultContainer.html(`<pre class="${response.success ? 'text-success' : 'text-danger'}">${formattedResponse}</pre>`);
                        
                        // Show toast notification
                        if (response.success) {
                            showToast('success', response.msg);
                        } else {
                            showToast('danger', response.msg);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Display error
                        resultContainer.html(`<pre class="text-danger">Error: ${status}\n${error}</pre>`);
                        showToast('danger', 'Failed to send test notification');
                    },
                    complete: function() {
                        // Re-enable button
                        button.prop('disabled', false).html('<i class="uil uil-bell"></i> <?= lang('Pages.Send_Test_Push_Notification') ?>');
                    }
                });
            });
        });

        /**
         * Handle the selected cache handler test
         */
        // Show/hide cache connection test button based on selected handler
        $('#cacheHandler').on('change', function() {
            const selectedHandler = $(this).val();
            if (selectedHandler === 'memcached' || selectedHandler === 'redis' || selectedHandler === 'predis') {
                $('#cache-connection-test-container').show();
            } else {
                $('#cache-connection-test-container').hide();
            }
        });
        
        // Trigger the change event to set initial visibility
        $('#cacheHandler').trigger('change');
        
        // Handle test button click
        $('#test-cache-connection-btn').on('click', function() {
            const button = $(this);
            const resultContainerWrapper = $('#cache-result-container-wrapper');
            const resultContainer = $('#cache-result-container');
            
            // Show the result area
            resultContainerWrapper.slideDown();
            
            // Disable button and show loading state
            button.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin mb-0 align-middle"></i> <?= lang('Pages.Testing') ?>');
            
            // Send AJAX request
            $.ajax({
                url: '<?= base_url('admin-options/global-settings/test-cache-connection') ?>',
                type: 'POST',
                data: {
                    handler: $('#cacheHandler').val()
                },
                dataType: 'json',
                success: function(response) {
                    // Format the details as a table if they exist
                    let detailsHtml = '';
                    
                    if (response.details && response.details.length > 0) {
                        // For memcached with multiple servers
                        detailsHtml = '<table class="table table-sm table-striped table-bordered mt-3">';
                        detailsHtml += '<thead><tr><th>Server</th><th>Version</th><th>Uptime</th><th>Connections</th><th>Memory</th></tr></thead>';
                        detailsHtml += '<tbody>';
                        
                        response.details.forEach(function(server) {
                            detailsHtml += '<tr>';
                            detailsHtml += `<td>${server.server}</td>`;
                            detailsHtml += `<td>${server.version}</td>`;
                            detailsHtml += `<td>${formatUptime(server.uptime)}</td>`;
                            detailsHtml += `<td>${server.curr_connections}</td>`;
                            detailsHtml += `<td>${formatBytes(server.bytes)}</td>`;
                            detailsHtml += '</tr>';
                        });
                        
                        detailsHtml += '</tbody></table>';
                    } else if (response.details && Object.keys(response.details).length > 0) {
                        // For redis with a single server
                        detailsHtml = '<table class="table table-sm table-striped table-bordered mt-3">';
                        detailsHtml += '<tbody>';
                        
                        for (const [key, value] of Object.entries(response.details)) {
                            const formattedKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            let formattedValue = value;
                            
                            if (key === 'uptime_in_seconds') {
                                formattedValue = formatUptime(value);
                            }
                            
                            detailsHtml += '<tr>';
                            detailsHtml += `<td><strong>${formattedKey}</strong></td>`;
                            detailsHtml += `<td>${formattedValue}</td>`;
                            detailsHtml += '</tr>';
                        }
                        
                        detailsHtml += '</tbody></table>';
                    }
                    
                    // Display the result
                    resultContainer.html(`
                        <div class="${response.success ? 'alert alert-success' : 'alert alert-danger'}">
                            ${response.msg}
                        </div>
                        ${detailsHtml}
                    `);
                    
                    // Show toast notification
                    if (response.success) {
                        showToast('success', response.msg);
                    } else {
                        showToast('danger', response.msg);
                    }
                },
                error: function(xhr, status, error) {
                    // Display error
                    resultContainer.html(`<div class="alert alert-danger">Error: ${status}<br>${error}</div>`);
                    showToast('danger', '<?= lang('Pages.Failed_to_test_cache_connection') ?>');
                },
                complete: function() {
                    // Re-enable button
                    button.prop('disabled', false).html('<i class="uil uil-link"></i> <?= lang('Pages.Test_Connection') ?>');
                }
            });
        });
        
        // Hide result container when close button is clicked
        $('#hide-cache-result-container').on('click', function() {
            $('#cache-result-container-wrapper').slideUp();
        });

        // Helper function to format uptime
        function formatUptime(seconds) {
            if (!seconds) return 'Unknown';
            
            seconds = parseInt(seconds);
            const days = Math.floor(seconds / 86400);
            seconds %= 86400;
            const hours = Math.floor(seconds / 3600);
            seconds %= 3600;
            const minutes = Math.floor(seconds / 60);
            seconds %= 60;
            
            let result = '';
            if (days > 0) result += days + 'd ';
            if (hours > 0) result += hours + 'h ';
            if (minutes > 0) result += minutes + 'm ';
            if (seconds > 0) result += seconds + 's';
            
            return result.trim();
        }
        
        // Helper function to format bytes
        function formatBytes(bytes) {
            if (!bytes) return 'Unknown';
            
            bytes = parseInt(bytes);
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes === 0) return '0 Byte';
            const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        }

        $('.mdi-close').click(function() {
            // Clear the value of the input field next to it
            var elementInput = $(this).closest('.form-icon').find('.form-control');
            elementInput.val('');
            elementInput.removeClass('is-invalid').removeClass('is-valid');
        });

        $('.mdi-refresh').click(function() {
            var tooltipElement = $(this).closest('[data-bs-toggle="tooltip"]');

            var elementID = $(this).closest('.form-icon').find('.form-control').attr('id');
            var requestURL = '<?= base_url('admin-options/global-settings/reset/') ?>' + elementID;

            var confirmDelete = confirm("<?= lang('Pages.Confirm_Delete_Message') ?>");

            if (confirmDelete) {
                $.ajax({
                    url: requestURL,
                    method: 'POST',
                    success: function (response) {
                        let toastType = 'info';
                        tooltipElement.tooltip('hide');

                        if (response.status == 1) {
                            toastType = 'success';
                            var newSrc;
                            if (response.appIcon) {
                                newSrc = response.appIcon;
                            } else if (response.appLogo_light) {
                                newSrc = response.appLogo_light;
                            } else if (response.appLogo_dark) {
                                newSrc = response.appLogo_dark;
                            } else if (response.PWA_App_icon_192x192) {
                                newSrc = response.PWA_App_icon_192x192;
                            } else if (response.PWA_App_icon_512x512) {
                                newSrc = response.PWA_App_icon_512x512;
                            } else if (response.push_notification_badge) {
                                newSrc = response.push_notification_badge;
                            } else  {
                                newSrc = '';
                            }
                            
                            $('.' + elementID +'Preview').attr('src', newSrc);
                        } else if (response.status == 2) {                
                            toastType = 'info';
                        } else {	
                            toastType = 'danger';
                        }
                        
                        showToast(toastType, response.msg);
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
		                showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }
                });
            }
        }); 
        
        $('.mdi-delete-circle-outline').click(function() {
            var tooltipElement = $(this).closest('[data-bs-toggle="tooltip"]');

            var requestURL = '<?= base_url('app-settings/delete-cookies') ?>';

            var confirmDelete = confirm("<?= lang('Pages.Confirm_delete_cookies') ?>");

            if (confirmDelete) {
                $.ajax({
                    url: requestURL,
                    method: 'GET',
                    success: function (response) {
                        let toastType = 'info';
                        tooltipElement.tooltip('hide');
                        
                        if (response.status == 1) {
                            toastType = 'success';
                        } else if (response.status == 2) {                
                            toastType = 'info';
                        } else {	
                            toastType = 'success';
                        }
                        
                        showToast(toastType, response.msg);
                    },
                    error: function (xhr, status, error) {
                        // Show error toast
		                showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
                    }										
                });
            }
        });             
    </script>   
<?= $this->endSection() //End section('scripts')?>