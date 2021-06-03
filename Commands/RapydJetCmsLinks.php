<?php

namespace App\Rapyd\Modules\Cms\Commands;

use Illuminate\Console\Command;

class RapydJetCmsLinks extends Command
{
  protected $signature   = 'rapyd:jet:cms:stepper-links';
  protected $description = 'Changes the CMS pages links to point to particular products in the stepper';

  public function __construct()
  {
    parent::__construct();
  }

  public function handle()
  {
    $url_matches  = self::url_slug_match();
    $dom_matches  = self::link_dom_match();
    $cms_pages    = \DB::table('cms_pages')->select('id', 'url_slug', 'content_body')->get();

    \RapydCore::benchmark('start', 'jet_cms_links' ,'s');
    $progress_bar = $this->output->createProgressBar($cms_pages->count());
    $progress_bar->start();
    foreach ($cms_pages as $page) {
      if ($url_matches['auto_dealer'][$page->url_slug] ?? false) {
        self::dom_manipulator($page, $url_matches['auto_dealer'][$page->url_slug], $dom_matches);
      } elseif ($url_matches['school_based'][$page->url_slug] ?? false) {
        self::dom_manipulator($page, $url_matches['school_based'][$page->url_slug], $dom_matches);
      } elseif ($url_matches['contractors'][$page->url_slug] ?? false) {
        self::dom_manipulator($page, $url_matches['contractors'][$page->url_slug], $dom_matches);
      } elseif ($url_matches['unorganized'][$page->url_slug] ?? false) {
        self::dom_manipulator($page, $url_matches['unorganized'][$page->url_slug], $dom_matches);
      } elseif ($url_matches['no_states'][$page->url_slug] ?? false) {
        self::dom_manipulator($page, $url_matches['no_states'][$page->url_slug], $dom_matches);
      } else {
        foreach ($url_matches['bypass_states'] as $state) {
          if (strpos($page->url_slug, $state) !== false) {
            self::dom_manipulator($page, false, $dom_matches, $state);
            break;
          }
        }
      }
      $progress_bar->advance();
    }
    $progress_bar->finish();
    $fn_time  = \RapydCore::benchmark('end', 'jet_cms_links', 's');
    $this->info('All JET CMS Pages Checked in '.$fn_time."\n");
  }

  protected function url_slug_match()
  {
    $auto_dealer = [
      "alabama/auto-dealer-bond"          => [4037, 4044],
      "alabama/auto-dealer-bonds"         => [4038, 4044],
      "arizona/auto-dealer-bond"          => [7074, 7199, 7207, 8647],
      "arizona/auto-dealer-bonds"         => [7075, 7199, 7207, 8647],
      "florida/auto-dealer-bonds"         => [443, 10433],
      "georgia/auto-dealer-bonds"         => [444],
      "indiana/auto-dealer-bonds"         => [7035],
      "kentucky/auto-dealer-bonds"        => "kentucky",
      "louisiana/auto-dealer-bonds"       => [2570],
      "mississippi/auto-dealer-bonds"     => "mississippi",
      "montana/auto-dealer-bonds"         => [7040],
      "nebraska/auto-dealer-bonds"        => [7045],
      "nevada/auto-dealer-bond"           => [7124, 2566, 7185, 7193],
      "nevada/auto-dealer-bonds"          => [7125, 2566, 7185, 7193],
      "new-mexico/auto-dealer-bonds"      => [457, 9181, 9182],
      "north-carolina/auto-dealer-bond"   => [454, 7141, 7174],
      "north-carolina/auto-dealer-bonds"  => [455, 7141, 7174],
      "north-dakota/auto-dealer-bonds"    => [7182],
      "ohio/auto-dealer-bond"             => [4588, 7894],
      "ohio/auto-dealer-bonds"            => [4589, 7894],
      "oklahoma/auto-dealer-bonds"        => [8819, 8830, 7077, 7119, 10261],
      "oregon/auto-dealer-bond"           => [2569],
      "oregon/auto-dealer-bonds"          => [2570],
      "texas/auto-dealer-bonds"           => [2618, 7324, 7346, 7350],
      "virginia/auto-dealer-bonds"        => "virginia",
      "west-virginia/auto-dealer-bonds"   => "west-virginia" 
    ];

    $school_based = [
      "alabama/cosmetology-school-bond"                           => [7678, 7679, 7680, 7681],
      "alabama/private-real-estate-school-bond"                   => [7678, 7679, 7680, 7681],
      "alabama/private-school-agent-bond"                         => [7678, 7679, 7680, 7681],
      "alabama/private-school-bond"                               => [7678, 7679, 7680, 7681],
      "alabama/school-bonds"                                      => "alabama",
      "arizona/barber-school-bond"                                => [4526],
      "arizona/cosmetology-school-bond"                           => [4526],
      "nevada/commercial-driver-training-company-and-school-bond" => [4758],
      "nevada/driving-school-bond"                                => [4758],
      "nevada/private-postsecondary-school-bond"                  => [7665],
      "nevada/private-school-bond"                                => [7637],
      "nevada/school-bonds"                                       => "nevada",
      "north-carolina/barber-school-bond"                         => [7614],
      "north-carolina/commercial-driver-training-school-bond"     => [4314, 4330],
      "north-carolina/cosmetology-school-bond"                    => [4537],
      "north-carolina/proprietary-school-bond"                    => [7668],
      "north-carolina/real-estate-school-bond"                    => [7669],
      "north-carolina/school-bonds"                               => "north-carolina",
      "ohio/cosmetology-and-barber-school-bond"                   => "ohio",
      "ohio/school-bonds"                                         => "ohio",
      "ohio/school-registration-bond"                             => [4183],
      "oregon/commercial-driving-school-bond"                     => [1275, 1276, 1277],
      "oregon/school-authorized-to-confer-degree-bond"            => [8144],
      "oregon/school-bonds"                                       => "oregon"
    ];

    // FOR NOW THESE WILL ALL GO TO STATE CONFIRMATION PAGES
    $contractors = [
      "alabama/auburn-contractor-bond"                              => "alabama",
      "alabama/birmingham-contractor-bond"                          => "alabama",
      "alabama/chickasaw-contractor-bond"                           => "alabama",
      "alabama/contractor-license-bonds"                            => "alabama",
      "alabama/dothan-contractor-bond"                              => "alabama",
      "alabama/eufaula-contractor-bond"                             => "alabama",
      "alabama/fairfield-contractor-bond"                           => "alabama",
      "alabama/gulf-shores-contractor-bond"                         => "alabama",
      "alabama/homewood-contractor-bond"                            => "alabama",
      "alabama/hvac-refrigeration-contractor-bond"                  => "alabama",
      "alabama/mobile-contractor-bond"                              => "alabama",
      "alabama/montgomery-contractor-bond"                          => "alabama",
      "alabama/mountain-brook-contractor-bond"                      => "alabama",
      "alabama/opelika-contractor-bond"                             => "alabama",
      "alabama/prichard-contractor-bond"                            => "alabama",
      "alabama/saraland-contractor-bond"                            => "alabama",
      "alabama/tuscaloosa-contractor-bond"                          => "alabama",
      "arizona/contractor-license-bond"                             => "arizona",
      "arizona/contractor-license-bonds"                            => "arizona",
      "arizona/contractor-taxpayer-bond"                            => "arizona",
      "florida/contractor-license-bonds"                            => "florida",
      "georgia/contractor-license-bonds"                            => "georgia",
      "nevada/contractor-license-bond"                              => "nevada",
      "nevada/contractor-license-bonds"                             => "nevada",
      "new-mexico/contractor-license-bonds"                         => "new-mexico",
      "north-carolina/greensboro-contractor-bond"                   => "north-carolina",
      "north-carolina/irrigation-contractor-bond"                   => "north-carolina",
      "north-carolina/landscape-contractor-bond"                    => "north-carolina",
      "north-carolina/manufactured-housing-set-up-contractor-bond"  => "north-carolina",
      "north-carolina/modular-building-set-up-contractor-bond"      => "north-carolina",
      "north-carolina/wake-county-well-contractor-bond"             => "north-carolina",
      "north-carolina/winston-salem-contractor-bond"                => "north-carolina",
      "ohio/aurora-contractor-bond"                                 => "ohio",
      "ohio/avon-contractor-bond"                                   => "ohio",
      "ohio/avon-lake-contractor-license-and-landscape-bond"        => "ohio",
      "ohio/bedford-contractor-bond"                                => "ohio",
      "ohio/bedford-heights-contractor-bond"                        => "ohio",
      "ohio/belpre-contractor-permit-bond"                          => "ohio",
      "ohio/bexley-contractor-bond"                                 => "ohio",
      "ohio/bowling-green-public-works-contractor-bond"             => "ohio",
      "ohio/brecksville-contractor-bond"                            => "ohio",
      "ohio/broadview-heights-contractor-bond"                      => "ohio",
      "ohio/brook-park-contractor-bond"                             => "ohio",
      "ohio/brunswick-contractor-bond"                              => "ohio",
      "ohio/canal-fulton-contractor-bond"                           => "ohio",
      "ohio/canton-contractor-bond"                                 => "ohio",
      "ohio/cheviot-concrete-contractor-bond"                       => "ohio",
      "ohio/chillicothe-contractor-compliance-bond"                 => "ohio",
      "ohio/cleveland-heights-contractor-bond"                      => "ohio",
      "ohio/dayton-contractor-bond"                                 => "ohio",
      "ohio/deer-park-contractor-bond"                              => "ohio",
      "ohio/delaware-contractor-bonds"                              => "ohio",
      "ohio/dover-contractor-bond"                                  => "ohio",
      "ohio/dublin-contractor-bond"                                 => "ohio",
      "ohio/east-cleveland-contractor-bond"                         => "ohio",
      "ohio/eastlake-contractor-bond"                               => "ohio",
      "ohio/elyria-contractor-bond"                                 => "ohio",
      "ohio/fairborn-contractor-bond"                               => "ohio",
      "ohio/findlay-contractor-bond"                                => "ohio",
      "ohio/garfield-heights-contractor-bond"                       => "ohio",
      "ohio/girard-contractor-bond"                                 => "ohio",
      "ohio/grandview-heights-contractor-bond"                      => "ohio",
      "ohio/greenfield-contractor-bond"                             => "ohio",
      "ohio/grove-city-contractor-bond"                             => "ohio",
      "ohio/hamilton-concrete-contractor-bond"                      => "ohio",
      "ohio/independence-contractor-bond"                           => "ohio",
      "ohio/kirtland-contractor-bond"                               => "ohio",
      "ohio/lancaster-contractor-bond"                              => "ohio",
      "ohio/lorain-contractor-bond"                                 => "ohio",
      "ohio/lyndhurst-contractor-bond"                              => "ohio",
      "ohio/macedonia-contractor-bond"                              => "ohio",
      "ohio/mansfield-contractor-bond"                              => "ohio",
      "ohio/private-water-system-contractor-bond"                   => "ohio",
      "ohio/school-bus-contract-bond"                               => "ohio",
      "ohio/sewage-treatment-system-contractor-bond"                => "ohio",
      "ohio/timber-sale-contract-bond"                              => "ohio",
      "oregon/construction-flagging-contractor-bond"                => "oregon",
      "oregon/contractor-license-bond"                              => "oregon",
      "oregon/contractor-license-bonds"                             => "oregon",
      "oregon/contractor-school-license-bond"                       => "oregon",
      "oregon/health-care-service-contractor-and-dental-and-optometrical-service-bond" => "oregon",
      "oregon/labor-contractor-bond"                                => "oregon",
      "oregon/landscape-contractor-bond"                            => "oregon",
      "oregon/other-contractor-bonds"                               => "oregon",
      "oregon/restricted-residential-contractor-license-bond"       => "oregon"
    ];

    $unorganized = [
      "alabama/alabaster-public-improvement-bond"                           => [3040],
      "alabama/alcohol-tax-bond"                                            => [3958, 3962],
      "alabama/appraisal-management-company-bond"                           => [1038],
      "alabama/auctioneer-bond"                                             => [1074],
      "alabama/automotive-dismantler-bond"                                  => [4044],
      "alabama/bail-bondsman-bond"                                          => [1141, 4053],
      "alabama/baldwin-concessionaire-bond"                                 => [4344],
      "alabama/bessemer-electrician-bond"                                   => [1685],
      "alabama/birmingham-land-disturbing-bond"                             => [4238, 1261, 5670, 10110],
      "alabama/chickasaw-small-loan-company-bond"                           => [3511],
      "alabama/conservator-bond"                                            => "alabama",
      "alabama/construction-bonds"                                          => "alabama",
      "alabama/construction-license-bonds"                                  => "alabama",
      "alabama/construction-permit-bonds"                                   => "alabama",
      "alabama/court-and-probate-bonds"                                     => "alabama",
      "alabama/department-of-transportation-bid-and-performance-bond"       => [8781],
      "alabama/dothan-bail-bondsman-license-bond"                           => [1141, 4053],
      "alabama/enterprise-electrician-bond"                                 => [1685],
      "alabama/environmental-bonds"                                         => "alabama",
      "alabama/executor-bond"                                               => "alabama",
      "alabama/fight-promoter-bond"                                         => [7739],
      "alabama/finance-bonds"                                               => [7600],
      "alabama/grain-dealer-warehousemans-bond"                             => [9495, 5249],
      "alabama/health-studio-bond"                                          => [5322],
      "alabama/helena-performance-and-repair-bond"                          => [7465],
      "alabama/hoover-earthwork-land-disturbing-bond"                       => [4238, 1261, 10557],
      "alabama/house-moving-bond"                                           => [5411, 1998],
      "alabama/houston-county-motor-fuel-distributor-bond"                  => [7059],
      "alabama/hueytown-house-moving-land-disturbing-bond"                  => [5411, 1998, 5670, 10110, 4238],
      "alabama/hunting-fishing-special-agent-bond"                          => [5433],
      "alabama/huntsville-retail-vendor-bond"                               => [7947],
      "alabama/ibew-local-136-union-wage-welfare-bond"                      => [9217],
      "alabama/investment-adviser-bond"                                     => [5640],
      "alabama/iron-workers-union-92-wage-welfare-bond"                     => [9217],
      "alabama/irondale-land-disturbing-bond"                               => [4238, 1261, 5670, 10110],
      "alabama/jackson-county-right-of-way-permit-bond"                     => [3148],
      "alabama/jefferson-county-building-permit-bond"                       => [1168, 4156, 4170, 4171],
      "alabama/jefferson-county-gasoline-tax-bond"                          => [5123],
      "alabama/lost-title-bond"                                             => [433],
      "alabama/manufactured-housing-bond"                                   => [6787, 6791, 6796],
      "alabama/marine-sanitation-device-inspector-bond"                     => [6810],
      "alabama/medicaid-DME-medical-supply-provider-bond"                   => [6840],
      "alabama/millwright-local-union-1192-wage-welfare-bond"               => [9217],
      "alabama/mobile-pawnbroker-bond"                                      => [2654],
      "alabama/mobile-right-of-way-permit-bond"                             => [3148],
      "alabama/money-transmitter-bond"                                      => [6945],
      "alabama/mortgage-broker-bond"                                        => [6972],
      "alabama/motor-club-bond"                                             => [4037],
      "alabama/motor-fuels-bond"                                            => "alabama",
      "alabama/natural-gas-bond"                                            => [4343],
      "alabama/oil-gas-well-bond"                                           => "alabama",
      "alabama/onsite-wastewater-bond"                                      => "alabama",
      "alabama/ozark-tree-cutter-stump-grinder-bond"                        => [3741, 3742],
      "alabama/patient-trust-fund-bond"                                     => "alabama",
      "alabama/pest-control-bond"                                           => [4575],
      "alabama/polygraph-examiner-bond"                                     => [2900],
      "alabama/pool-room-operator-bond"                                     => [2911],
      "alabama/premium-finance-company-bond"                                => [7600],
      "alabama/preneed-funeral-bond"                                        => [1040],
      "alabama/private-investigator-detective-agency-security-guard-bond"   => [2940, 2961],
      "alabama/professional-employer-organization-bond"                     => [7708],
      "alabama/professional-fundraiser-bond"                                => [7729],
      "alabama/residential-roofer-bond"                                     => [3111],
      "alabama/sales-tax-bond"                                              => [8133],
      "alabama/scrap-tire-transporter-bond"                                 => [10490],
      "alabama/surface-mining-bond"                                         => "alabama",
      "alabama/surplus-line-broker-bond"                                    => [8575],
      "alabama/tax-bonds"                                                   => "alabama",
      "alabama/telemarketing-bond"                                          => "alabama",
      "alabama/tobacco-consignment-bond"                                    => [8689],
      "alabama/transient-merchant-bond"                                     => [8723],
      "alabama/transportation-bonds"                                        => [8781],
      "alabama/tuscaloosa-county-right-of-way-permit-bond"                  => [3148],
      "alabama/underground-storage-facility-bond"                           => "alabama",
      "alabama/unemployment-compensation-bond"                              => [9620],
      "alabama/union-bonds"                                                 => [9217],
      "alabama/utility-deposit-bond"                                        => [8838],
      "alabama/vacation-timesharing-broker-bond"                            => [9176, 9177],
      "arizona/aircraft-dealer-bond"                                        => [3945],
      "arizona/appraisal-management-company-bond"                           => "arizona",
      "arizona/authorized-third-party-provider-bond"                        => [8647],
      "arizona/bail-bond-agent-bond"                                        => [4052],
      "arizona/boxing-promoter-bond"                                        => [4124],
      "arizona/collection-agency-bond"                                      => [4249],
      "arizona/conservator-and-guardian-bond"                               => "arizona",
      "arizona/construction-bonds"                                          => "arizona",
      "arizona/court-and-probate-bonds"                                     => "arizona",
      "arizona/debt-management-bond"                                        => [4602],
      "arizona/electronic-records-access-bond"                              => [4840],
      "arizona/escrow-agent-bond"                                           => [4932],
      "arizona/executor-bond"                                               => "arizona",
      "arizona/fiduciary-certification-bond"                                => [7721],
      "arizona/finance-bonds"                                               => "arizona",
      "arizona/home-inspector-bond"                                         => [5401],
      "arizona/ifta-bond"                                                   => "arizona",
      "arizona/ignition-interlock-installer-bond"                           => [5454],
      "arizona/life-and-health-insurance-administrator-bond"                => [6564],
      "arizona/liquor-luxury-privilege-tax-bond"                            => [6690],
      "arizona/lost-title-bond"                                             => [431],
      "arizona/manufactured-mobile-home-bond"                               => [7949],
      "arizona/maricopa-county-right-of-way-bond"                           => [7992],
      "arizona/money-transmitter-bond"                                      => [6946],
      "arizona/mortgage-banker-bond"                                        => [6971, 4323],
      "arizona/mortgage-broker-bond"                                        => [6972, 4323],
      "arizona/motor-fuel-supplier-bond"                                    => [7074],
      "arizona/motor-vehicle-recycler-bond"                                 => [7199],
      "arizona/peddler-bond"                                                => "arizona",
      "arizona/phoenix-right-of-way-bond"                                   => [7992],
      "arizona/private-investigator-bond"                                   => [2957],
      "arizona/private-postsecondary-education-bond"                        => [7663, 7664],
      "arizona/reclamation-and-damage-bond"                                 => "arizona",
      "arizona/sales-tax-bonds"                                             => "arizona",
      "arizona/securities-dealer-bond"                                      => [4589],
      "arizona/self-insurance-workers-compensation-bond"                    => [10508],
      "arizona/service-company-bond"                                        => [10541],
      "arizona/telephone-solicitation-bond"                                 => [10724],
      "arizona/transportation-bonds"                                        => "arizona",
      "arizona/tucson-auctioneer-bond"                                      => [1073],
      "arizona/tucson-temporary-certificate-of-occupancy-performance-bond"  => [8620],
      "arizona/tucson-temporary-use-permit-bond"                            => "arizona",
      "arizona/utility-deposit-bond"                                        => "arizona",
      "arizona/wholesale-pharmacy-bond"                                     => [10034, 10287],
      "nevada/auctioneer-bond"                                              => "nevada",
      "nevada/bail-agent-license-bond"                                      => [5127],
      "nevada/body-shop-and-garage-bond"                                    => [7124, 7185],
      "nevada/check-cashing-deferred-deposit-bond"                          => [4625],
      "nevada/clark-county-alarm-installer-bond"                            => [1208],
      "nevada/collection-agency-bond"                                       => [5070],
      "nevada/conservator-and-guardian-bond"                                => "nevada",
      "nevada/construction-bonds"                                           => "nevada",
      "nevada/court-and-probate-bonds"                                      => "nevada",
      "nevada/covered-service-provider-bond"                                => [4540],
      "nevada/credit-service-organization-bond"                             => [9867],
      "nevada/debt-management-services-bond"                                => [4612],
      "nevada/document-preparation-service-bond"                            => [4700],
      "nevada/douglas-county-traveling-merchant-bond"                       => "nevada",
      "nevada/emissions-inspection-station-bond"                            => [9195],
      "nevada/employee-leasing-company-bond"                                => "nevada",
      "nevada/escrow-agency-bond"                                           => [4930],
      "nevada/executor-bond"                                                => "nevada",
      "nevada/fight-promoter-bond"                                          => [7741],
      "nevada/finance-bonds"                                                => "nevada",
      "nevada/fuel-tax-bond"                                                => [5078, 5079, 5080],
      "nevada/funeral-seller-and-cemetery-seller-bond"                      => [10037],
      "nevada/health-care-facilities-and-services-bond"                     => [5300],
      "nevada/las-vegas-license-permit-bond"                                => "nevada",
      "nevada/las-vegas-secondhand-dealer-bond"                             => [3233],
      "nevada/las-vegas-temporary-merchant-bond"                            => "nevada",
      "nevada/liquor-tax-bond"                                              => [6586],
      "nevada/livestock-dealer-broker-commission-merchant-bond"             => [4593],
      "nevada/lost-title-bond"                                              => "nevada",
      "nevada/managing-general-agent-bond"                                  => [6754],
      "nevada/money-transmitter-bond"                                       => "nevada",
      "nevada/mortgage-banker-bond"                                         => [5019],
      "nevada/mortgage-company-bond"                                        => [6992],
      "nevada/mortgage-servicer-bond"                                       => [7029],
      "nevada/motor-vehicle-damage-appraiser-bond"                          => [2566],
      "nevada/motor-vehicle-wrecker-and-salvage-pool-bond"                  => [9577, 8136],
      "nevada/notary-bond"                                                  => "nevada",
      "nevada/oil-gas-geothermal-well-drilling-bond"                        => "nevada",
      "nevada/pharmaceutical-wholesaler-bond"                               => [7524],
      "nevada/premium-finance-company-bond"                                 => "nevada",
      "nevada/private-employment-agency-bond"                               => "nevada",
      "nevada/public-weighmaster-bond"                                      => [3055],
      "nevada/reno-excavation-bond"                                         => [1733, 1752],
      "nevada/right-of-way-occupancy-bond"                                  => [8028],
      "nevada/sales-tax-bond"                                               => [8122],
      "nevada/self-insured-employer-bond"                                   => "nevada",
      "nevada/specialty-license-plate-bond"                                 => [8408],
      "nevada/tax-bonds"                                                    => "nevada",
      "nevada/telecommunications-service-provider-bond"                     => "nevada",
      "nevada/third-party-administrator-bond"                               => [10736],
      "nevada/title-agent-or-insurer-bond"                                  => [8671],
      "nevada/tobacco-sales-tax-and-stamp-bond"                             => "nevada",
      "nevada/transportation-bonds"                                         => "nevada",
      "nevada/utility-deposit-bond"                                         => "nevada",
      "nevada/vehicle-registration-program-bond"                            => [9194, 9195],
      "nevada/wage-and-welfare-bond"                                        => "nevada",
      "nevada/washoe-county-excavation-bond"                                => [1733, 1752],
      "north-carolina/alcohol-tax-bond"                                     => [3981],
      "north-carolina/appraisal-management-company-bond"                    => "north-carolina",
      "north-carolina/asheville-erosion-control-bond"                       => [4900, 5244],
      "north-carolina/buncombe-county-stormwater-management-bond"           => [10617, 10615, 10625],
      "north-carolina/business-opportunity-bond"                            => "north-carolina",
      "north-carolina/cemetery-company-performance-bond"                    => [9655],
      "north-carolina/chapel-hill-land-improvement-bond"                    => [5244],
      "north-carolina/charlotte-land-development-bond"                      => [5668, 8555],
      "north-carolina/collection-agency-bond"                               => "north-carolina",
      "north-carolina/construction-bonds"                                   => "north-carolina",
      "north-carolina/court-and-probate-bonds"                              => "north-carolina",
      "north-carolina/credit-repair-service-bond"                           => [4548],
      "north-carolina/criminal-background-data-extract-bond"                => [9872],
      "north-carolina/davidson-county-zoning-ordinance-reclamation-bond"    => "north-carolina",
      "north-carolina/designated-agent-bond"                                => [1526],
      "north-carolina/discount-buying-club-bond"                            => [9892],
      "north-carolina/durham-county-sedimentation-and-erosion-control-bond" => [10499],
      "north-carolina/durham-county-stormwater-management-bond"             => [10617, 10615, 10625],
      "north-carolina/durham-land-development-bond"                         => [5668, 8555],
      "north-carolina/environmental-bonds"                                  => "north-carolina",
      "north-carolina/executor-bond"                                        => "north-carolina",
      "north-carolina/fight-promoter-bond"                                  => [4109, 4110, 4111],
      "north-carolina/finance-bonds"                                        => "north-carolina",
      "north-carolina/fuel-tax-bond"                                        => [7110],
      "north-carolina/grain-dealer-bond"                                    => "north-carolina",
      "north-carolina/greensboro-house-moving-bond"                         => [5423, 5424],
      "north-carolina/greensboro-sedimentation-control-bond"                => [10499],
      "north-carolina/greensboro-stormwater-management-bond"                => [10615, 10617, 10625],
      "north-carolina/greensboro-subdivision-bond"                          => [8555, 10684, 10686],
      "north-carolina/guardian-bond"                                        => "north-carolina",
      "north-carolina/guilford-county-land-improvement-bond"                => [5675, 5244],
      "north-carolina/haywood-county-land-disturbing-bond"                  => [5671],
      "north-carolina/health-club-and-prepaid-entertainment-service-bond"   => [7608],
      "north-carolina/high-point-erosion-control-bond"                      => [10499, 5244],
      "north-carolina/high-point-land-improvement-bond"                     => [5675, 5244],
      "north-carolina/highway-encroachment-continuing-indemnity-bond"       => [5343, 5344, 5345],
      "north-carolina/highway-encroachment-performance-and-indemnity-bond"  => [5343, 5344, 5345],
      "north-carolina/home-inspector-bond"                                  => [5399],
      "north-carolina/huntersville-land-development-bond"                   => [5668, 8555],
      "north-carolina/hunting-fishing-license-agent-bond"                   => [2022],
      "north-carolina/insurance-broker-bond"                                => [5598, 2082],
      "north-carolina/invention-developer-bond"                             => [10099],
      "north-carolina/investment-adviser-bond"                              => "north-carolina",
      "north-carolina/itinerant-merchant-bond"                              => [2117, 2722, 2723],
      "north-carolina/jackson-county-erosion-control-bond"                  => [4900, 5244, 10499],
      "north-carolina/job-listing-service-bond"                             => "north-carolina",
      "north-carolina/land-reclamation-bond"                                => "north-carolina",
      "north-carolina/lexington-local-union-312-wage-and-welfare-bond"      => "north-carolina",
      "north-carolina/liquor-transportation-bond"                           => [8785],
      "north-carolina/loan-broker-bond"                                     => "north-carolina",
      "north-carolina/lost-title-bond"                                      => [454],
      "north-carolina/lottery-bond"                                         => [6723],
      "north-carolina/manufactured-housing-dealer-bond"                     => [6785],
      "north-carolina/manufactured-housing-manufacturer-bond"               => [6785],
      "north-carolina/mecklenburg-county-financial-guarantee-bond"          => [3865],
      "north-carolina/mecklenburg-county-land-use-and-construction-bond"    => [5681],
      "north-carolina/mecklenburg-county-subdivision-bond"                  => [8555, 10684, 10686],
      "north-carolina/money-transmitter-bond"                               => "north-carolina",
      "north-carolina/mortgage-lender-bond"                                 => [10463],
      "north-carolina/mortgage-servicer-bond"                               => "north-carolina",
      "north-carolina/motor-club-bond"                                      => "north-carolina",
      "north-carolina/oversize-overweight-permit-bond"                      => [7351],
      "north-carolina/patient-trust-fund-bond"                              => "north-carolina",
      "north-carolina/pawnbroker-bond"                                      => [2668, 7390, 2664],
      "north-carolina/permit-bonds"                                         => "north-carolina",
      "north-carolina/pinehurst-land-improvement-bond"                      => [10566, 10573],
      "north-carolina/precious-metals-dealer-bond"                          => [7593],
      "north-carolina/premium-finance-company-bond"                         => "north-carolina",
      "north-carolina/preneed-funeral-bond"                                 => [10397],
      "north-carolina/private-personnel-service-bond"                       => [7658],
      "north-carolina/professional-employer-organization-bond"              => "north-carolina",
      "north-carolina/professional-solicitor-bond"                          => [2723, 8358],
      "north-carolina/property-warranty-bond"                               => [10817],
      "north-carolina/public-adjuster-bond"                                 => "north-carolina",
      "north-carolina/public-utility-service-provider-bond"                 => [10428, 10429],
      "north-carolina/raleigh-land-improvement-bond"                        => [5675, 5244],
      "north-carolina/raleigh-right-of-way-bond"                            => [4243],
      "north-carolina/reverse-mortgage-lender-bond"                         => [10463],
      "north-carolina/rowan-county-subdivision-bond"                        => [10686, 10684, 8555],
      "north-carolina/sales-tax-bonds"                                      => "north-carolina",
      "north-carolina/self-insurer-workers-compensation-bond"               => [10518],
      "north-carolina/southern-shores-permit-bond"                          => [1210],
      "north-carolina/statement-of-bonding-ability"                         => [3574, 3575],
      "north-carolina/surplus-lines-agent-bond"                             => [8574, 8586],
      "north-carolina/tenants-security-deposit-bond"                        => [8625],
      "north-carolina/third-party-cdl-testing-bond"                         => [4307],
      "north-carolina/tobacco-tax-bond"                                     => [4207, 3698, 8695],
      "north-carolina/transportation-bonds"                                 => [8783, 8785],
      "north-carolina/transportation-broker-bond"                           => [8783, 8785],
      "north-carolina/tuition-guaranty-bond"                                => [7267],
      "north-carolina/union-county-landfill-credit-account-bond"            => [5687],
      "north-carolina/utility-deposit-bond"                                 => [9165],
      "north-carolina/winston-salem-and-forsyth-county-site-improvement-bond" => [10566, 10573],
      "ohio/akron-site-improvement-bond"                                    => "ohio",
      "ohio/apple-valley-compliance-bond"                                   => "ohio",
      "ohio/athens-garbage-hauler-bond"                                     => [1815],
      "ohio/athlete-agent-bond"                                             => [1815],
      "ohio/auctioneer-bond"                                                => [7308],
      "ohio/autism-scholarship-program-bond"                                => [4027],
      "ohio/blue-ash-excavation-bond-and-soil-erosion-bond"                 => [3990, 1031, 1066, 1761, 1761],
      "ohio/boat-registration-agent-bond"                                   => "ohio",
      "ohio/bricklayers-and-allied-craftworkers-wage-and-welfare-bond"      => [9480, 9483],
      "ohio/brine-transporter-bond"                                         => [9622],
      "ohio/brown-township-license-permit-bond"                             => [1366, 1434, 2169, 3198],
      "ohio/bucyrus-sewer-tapper-bond"                                      => "ohio",
      "ohio/building-laborers-wage-and-welfare-bond"                        => [5709, 5711],
      "ohio/carpenters-wage-and-welfare-bond"                               => [5743, 10299, 5727],
      "ohio/chardon-right-of-way-bond"                                      => [3201],
      "ohio/cigarette-tax-stamp-bond"                                       => [3522],
      "ohio/clayton-excavation-and-restoration-bond"                        => [3212],
      "ohio/columbus-license-permit-bond"                                   => "ohio",
      "ohio/conservator-and-guardian-bond"                                  => [3497],
      "ohio/construction-bonds"                                             => "ohio",
      "ohio/court-and-probate-bonds"                                        => "ohio",
      "ohio/credit-services-organization-bond"                              => [4550],
      "ohio/deputy-registrar-bond"                                          => [4662],
      "ohio/dog-breeder-bond"                                               => [9679],
      "ohio/driver-training-testing-license-bond"                           => "ohio",
      "ohio/environmental-bonds"                                            => "ohio",
      "ohio/excess-load-special-hauling-permit-bond"                        => [5002, 10271],
      "ohio/executor-bond"                                                  => "ohio",
      "ohio/fight-promoter-bond"                                            => [4127],
      "ohio/finance-bonds"                                                  => "ohio",
      "ohio/green-road-opening-and-driveway-bond"                           => "ohio",
      "ohio/greenville-right-of-way-bond"                                   => [7964],
      "ohio/hemp-processor-bond"                                            => [10074],
      "ohio/highland-heights-right-of-way-bond"                             => [7964],
      "ohio/highway-right-of-way-permit-bond"                               => [10079, 10080],
      "ohio/huber-heights-excavation-and-restoration-bond"                  => [1031],
      "ohio/hudson-right-of-way-bond"                                       => [7964],
      "ohio/hunting-fishing-license-bond"                                   => "ohio",
      "ohio/ibew-local-union-wage-and-welfare-bond"                         => [9480, 9483],
      "ohio/international-union-of-painters-and-allied-trades-wage-and-welfare-bond"  => "ohio",
      "ohio/iron-workers-union-wage-and-welfare-bond"                       => "ohio",
      "ohio/IUPAT-district-6-wage-and-welfare-bond"                         => "ohio",
      "ohio/kent-right-of-way-bond-and-well-construction-bond"              => "ohio",
      "ohio/kettering-excavation-bond"                                      => [3990, 1031, 1066, 1761, 2817],
      "ohio/lebanon-right-of-way-bond"                                      => [7964],
      "ohio/lebanon-road-performance-bond-district-eight"                   => [8100],
      "ohio/local-union-18-wage-and-welfare-bond"                           => [9480, 9483],
      "ohio/lottery-sales-retailer-bond"                                    => [6741],
      "ohio/manufactured-home-broker-dealer-installer-bond"                 => [6761, 6771],
      "ohio/maple-heights-license-permit-bond"                              => [1366, 1434],
      "ohio/medical-marijuana-dispensary-bond"                              => [10189],
      "ohio/medina-special-hauling-permit-bond"                             => [8392],
      "ohio/milk-dealer-bond"                                               => [6910],
      "ohio/money-transmitter-bond"                                         => "ohio",
      "ohio/motor-fuel-dealer-bond"                                         => [4588],
      "ohio/nursing-facility-residents-fund-bond"                           => [7281],
      "ohio/oil-and-gas-well-driller-bond"                                  => "ohio",
      "ohio/pawnbroker-bond"                                                => "ohio",
      "ohio/plasterers-and-cement-masons-wage-and-welfare-bond"             => "ohio",
      "ohio/plumbers-pipefitters-union-wage-and-welfare-bond"               => "ohio",
      "ohio/precious-metals-dealer-bond"                                    => [7595],
      "ohio/professional-employer-organization-bond"                        => "ohio",
      "ohio/public-adjuster-bond"                                           => [3044],
      "ohio/reclamation-permit-bond"                                        => "ohio",
      "ohio/remanufactured-vehicle-dealer-bond"                             => [7894],
      "ohio/residential-mortgage-lending-act-bond"                          => "ohio",
      "ohio/securities-dealer-bond"                                         => [8170],
      "ohio/sheet-metal-workers-union-wage-and-welfare-bond"                => "ohio",
      "ohio/short-term-lender-bond"                                         => [10554],
      "ohio/solicitor-fundraiser-counsel-bond"                              => "ohio",
      "ohio/solid-waste-facility-and-scrap-tire-transporter-bond"           => "ohio",
      "ohio/surplus-lines-broker-bond"                                      => "ohio",
      "ohio/tax-bonds"                                                      => "ohio",
      "ohio/telephone-solicitor-bond"                                       => "ohio",
      "ohio/title-agent-bond"                                               => "ohio",
      "ohio/transient-vendor-bond"                                          => "ohio",
      "ohio/transportation-bonds"                                           => [10486],
      "ohio/unemployment-compensation-fund-bond"                            => [10769],
      "ohio/union-bonds"                                                    => "ohio",
      "ohio/utility-deposit-bond"                                           => [10065],
      "ohio/vendor-sales-tax-bond"                                          => "ohio",
      "ohio/viatical-settlement-provider-bond"                              => [10798]
    ];

    $no_states = [
      "wage-and-welfare/sprinkler-fitter-union-bond",
      "appeal-bond",
      "business-service-bond",
      "business-services-bond",
      "conservator-guardian-bond",
      "counter-replevin-bond",
      "court-bonds",
      "employee-dishonesty-bond",
      "employee-dishonesty-bond/apply",
      "erisa-bond",
      "executor-bond",
      "fidelity-bonds",
      "financial-institution-bond",
      "freight-broker-bond",
      "fuel-supply-payment-bond",
      "janitorial-bond",
      "janitorial-services-bond",
      "lien-bond",
      "notary-bonds",
      "probate-bonds",
      "registration",
      "replevin-bond",
      "trustee-bond"
    ];

    $bypass_states = [
      "alaska",
      "arkansas",
      "california",
      "colorado",
      "connecticut",
      "delaware",
      "hawaii",
      "idaho",
      "illinois",
      "iowa",
      "kansas",
      "maine",
      "maryland",
      "massachusetts",
      "michigan",
      "minnesota",
      "missouri",
      "new-hampshire",
      "new-jersey",
      "new-york",
      "pennsylvania",
      "rhode-island",
      "south-carolina",
      "south-dakota",
      "tennessee",
      "utah",
      "vermont",
      "washington",
      "wisconsin",
      "wyoming"
    ];

    return [
      'auto_dealer'   => $auto_dealer,
      'school_based'  => $school_based,
      'contractors'   => $contractors,
      'unorganized'   => $unorganized,
      'no_states'     => $no_states,
      'bypass_states' => $bypass_states
    ];
  }

  protected function link_dom_match()
  {
    $dom_matches = [
      /** 
       * REGEX PATTERN
      (<button>[\s|<a]+href=[\'\"])(https:\/\/app\.jetsurety\.com)(\/quote)([\/\'\"\s]+>)([a-zA-Z0-9\s]+)(<\/a>)
       
       * GROUP CAPTURE
       3 for url_path
       5 for inner_text
        
       * ORIGINAL TEXT
       <button><a href="https://app.jetsurety.com/quote/">APPLY NOW</a></button>
      **/
      [
        'type'    => '35',
        'pattern' => '/(<button>[\s|<a]+href=[\'\"])(https:\/\/app\.jetsurety\.com)(\/quote)([\/\'\"\s]+>)([a-zA-Z0-9\s]+)(<\/a>)/mi',
        'txt_1'  => '<button><a href="',
        'txt_2'  => '">',
        'txt_3'  => '</a></button>'
      ],
      /**
       * REGEX PATTERN
       (<a\s+href=[\'\"])(https:\/\/app\.jetsurety\.com)(\/quote)([\/\'\"\s]+class="apply-now-btn">)([a-zA-Z0-9\s]+)(<\/a>)

       * GROUP CAPTURE
       3 for url_path
       5 for inner_text

       * ORIGINAL TEXT
       <a href="https://app.jetsurety.com/quote/" class="apply-now-btn">APPLY NOW</a>
      **/
      [
        'type'    => '35',
        'pattern' => '/(<a\s+href=[\'\"])(https:\/\/app\.jetsurety\.com)(\/quote)([\/\'\"\s]+class="apply-now-btn">)([a-zA-Z0-9\s]+)(<\/a>)/mi',
        'txt_1'  => '<a href="',
        'txt_2'  => '" class="apply-now-btn">',
        'txt_3'  => '</a>'
      ],
      /**
       * REGEX PATTERN
       (<a\s+class=")([apply\-now lrg]+)("\s+title="main-green"\s+href=[\'\"])(https:\/\/app\.jetsurety\.com)(\/quote)([\/\'\"\s]+)>([a-zA-Z0-9\s]+)(<\/a>)

       * GROUP CAPTURE
       5 for url_path
       7 for inner_text

       * ORIGINAL TEXT
       <a class="apply-now lrg" title="main-green" href="https://app.jetsurety.com/quote/">PURCHASE BOND</a>
      **/
      [
        'type'    => '57',
        'pattern' => '/(<a\s+class=")([apply\-now lrg]+)("\s+title="main-green"\s+href=[\'\"])(https:\/\/app\.jetsurety\.com)(\/quote)([\/\'\"\s]+)>([a-zA-Z0-9\s]+)(<\/a>)/mi',
        'txt_1'  => '<a class="apply-now lrg" href="',
        'txt_2'  => '">',
        'txt_3'  => '</a>'
      ],
      /**
       * REGEX PATTERN
       (<a\s+class=")([apply\-now lrg]+)("\s+title="main-green"\s+href=[\'\"])(https:\/\/app\.jetsurety\.com)(\/quote)([\/\'\"\s]+)>([a-zA-Z0-9\s]+)(<\/a>)

       * GROUP CAPTURE
       5 for url_path
       7 for inner_text

       * ORIGINAL TEXT
        <p class="apply-now lrg"><a title="main-green" href="https://app.jetsurety.com/quote/">PURCHASE AUTO DEALER BOND</a></p>
      **/
      [
        'type'    => '57',
        'pattern' => '/(<p\s+class=")([apply\-now lrg]+)("><a\s+title="main-green"\s+href=[\'\"])(https:\/\/app\.jetsurety\.com)(\/quote)([\/\'\"\s]+)>([a-zA-Z0-9\s]+)(<\/a><\/p>)/mi',
        'txt_1'  => '<a class="apply-now lrg" href="',
        'txt_2'  => '">',
        'txt_3'  => '</a>'
      ]
    ];

    return $dom_matches;
  }

  protected function dom_manipulator($page, $url_match, $dom_match_check, $use_state = false)
  {
    $page_content = $page->content_body;

    if ($use_state) {
      $url_path   = '/bondquote?stepper_state='.$use_state;
      $inner_text = 'Apply for '.ucwords(str_replace('-', ' ', $use_state));
    } else {
      if(is_array($url_match)) {
        $multiple_buttons = [];
        
        foreach ($url_match as $bond_id) {
          $bond_info = \m_BondLibraries::find($bond_id);
          $multiple_buttons[] = [
            'url'   => '/bondquote?bond_ref_id='.$bond_id,
            'inner' => 'Apply for a '. $bond_info->description . ' Bond' 
          ];
        }
      } else {
        $url_path   = '/bondquote?stepper_state='.$url_match;
        $inner_text = 'Apply for '.ucwords(str_replace('-', ' ', $url_match));
      }
    }

    // Match types are set up for capture group usage which is not utilized at this moment
    foreach ($dom_match_check as $match) {
      if ($multiple_buttons ?? false) {
        $replace_str = '';
        foreach ($multiple_buttons as $btn) {
          $replace_str .= $match['txt_1'] . $btn['url'] . $match['txt_2'] . $btn['inner'] . $match['txt_3'];
        }
      } else {
        if ($match['type'] === '35') {
          $replace_str = $match['txt_1'] . $url_path . $match['txt_2'] . $inner_text . $match['txt_3'];
        } elseif ($match['type'] === '57') {
          $replace_str = $match['txt_1'] . $url_path . $match['txt_2'] . $inner_text . $match['txt_3'];
        }
      }

      $generic_pattern  = '/https:\/\/app\.jetsurety\.com\/quote\//i';
      $page_content     = preg_replace($match['pattern'], $replace_str, $page_content);
    }

    // Make sure to grab generic calls also
    $page_content = str_replace('https://app.jetsurety.com/quote', '/bondquote', $page_content);

    \DB::table('cms_pages')->where('id',$page->id)->update(['content_body' => $page_content]);
  }
}
