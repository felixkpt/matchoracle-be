<?php

namespace App\Utilities;

use App\Repositories\GameComposer;
use Carbon\Carbon;

trait FormatPredictionTrait
{
    function prediction_strategy($q)
    {
        $strategy = $q->{$this->predictionTypeMode};
        $updated_at = $strategy ? Carbon::parse($strategy->updated_at)->diffForHumans() : 'N/A';
        return [
           'prediction_strategy' => $q->{$this->predictionTypeMode},
           'Predicted' => $updated_at,
        ];
    }

    protected function formatFTHDAProba($q)
    {
        $class = 'border-start text-dark';

        $pred = $q->{$this->predictionTypeMode};
        $str = '-';
        if ($pred && $pred->ft_home_win_proba) {
            $str = $pred->ft_home_win_proba . '%, ' . $pred->ft_draw_proba . '%, ' . $pred->ft_away_win_proba . '%';
        }

        return '<div class="border-4 ps-1 text-nowrap ' . $class . ' d-inline-block">' . $str . '</div>';
    }

    protected function formatFTHDAPick($q)
    {
        $class = 'bg-light-blue text-dark';

        $pred = $q->{$this->predictionTypeMode};
        $str = '-';
        if ($pred && $pred->ft_home_win_proba) {
            $str = $pred->ft_hda_pick == 0 ? '1' : ($pred->ft_hda_pick == 1 ? 'X' : '2');
            $has_res = GameComposer::hasResults($q);
            $res = GameComposer::winningSide($q, true);

            if ($has_res) {
                if ($pred->ft_hda_pick == $res) {
                    $class = 'border-bottom bg-success text-white';
                } elseif ($pred) {
                    $class = 'border-bottom border-danger text-danger';
                }
            }
        }

        return '<div class="rounded-circle border p-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $str . '</div>';
    }

    protected function formatHTHDAProba($q)
    {
        $class = 'border-start text-dark';

        $pred = $q->{$this->predictionTypeMode};
        $str = '-';
        if ($pred && $pred->ht_home_win_proba) {
            $str = $pred->ht_home_win_proba . '%, ' . $pred->ht_draw_proba . '%, ' . $pred->ht_away_win_proba . '%';
        }

        return '<div class="border-4 ps-1 text-nowrap ' . $class . ' d-inline-block">' . $str . '</div>';
    }

    protected function formatHTHDAPick($q)
    {
        $class = 'bg-light-blue text-dark';

        $pred = $q->{$this->predictionTypeMode};
        $str = '-';
        if ($pred && $pred->ht_home_win_proba) {
            $str = $pred->ht_hda_pick == 0 ? '1' : ($pred->ht_hda_pick == 1 ? 'X' : '2');
            $has_res = GameComposer::hasResultsHT($q);
            $res = GameComposer::winningSideHT($q, true);

            if ($has_res) {
                if ($pred->ht_hda_pick == $res) {
                    $class = 'border-bottom bg-success text-white';
                } elseif ($pred) {
                    $class = 'border-bottom border-danger text-danger';
                }
            }
        }

        return '<div class="rounded-circle border p-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $str . '</div>';
    }

    protected function formatBTS($q)
    {

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->{$this->predictionTypeMode};
        $str = '-';
        if ($pred) {
            $str = $pred->bts_pick == 1 ? 'YES' : 'NO';
            $has_res = GameComposer::hasResults($q);
            $res = GameComposer::bts($q, true);

            if ($pred->bts_pick == $res) {
                $class = 'border-bottom border-success';
            } elseif ($pred && $has_res) {
                $class = 'border-bottom border-danger';
            }
        }


        return '<div class="border-2 py-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $str . '</div>';
    }

    protected function formatGoals($q)
    {

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->{$this->predictionTypeMode};

        $str = '-';
        if ($pred) {
            $str = $pred->over_under25_pick == 1 ? 'OV' : 'UN';
            $has_res = GameComposer::hasResults($q);
            $res = GameComposer::goals($q, true);

            if ($has_res) {
                if ($pred->over_under25_pick == 1 && $res > 2) {
                    $class = 'border-bottom border-success';
                } elseif ($pred->over_under25_pick == 0 && $res <= 2) {
                    $class = 'border-bottom border-success';
                } else {
                    $class = 'border-bottom border-danger';
                }
            }
        }

        return '<div class="border-2 py-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $str . '</div>';
    }

    protected function formatCS($q)
    {

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->{$this->predictionTypeMode};

        $str = '';
        if ($pred) {

            $q->prediction = $q->{$this->predictionTypeMode};
            $has_res = GameComposer::hasResults($q);
            $res = GameComposer::cs($q);

            if ($has_res && $res) {
                $class = 'border-bottom border-success';
            }

            $cs = array_search($pred->cs, scores());
            if ($cs != -1) {
                $str = $cs;
            }
        }

        return '<div class="border-2 py-1 text-nowrap ' . $class . ' d-inline-block text-center results-icon-md">' . $str . '</div>';
    }

    protected function formatHTScores($q)
    {

        $class = 'border-start text-dark';

        return '<div class="scores-sec border-4 p-1 text-nowrap ' . $class . ' d-inline-block text-center results-icon-md">' . $q->half_time . '</div>';
    }

    protected function formatFTScores($q)
    {

        $class = 'border-start text-dark';

        return '<div class="scores-sec border-4 p-1 text-nowrap ' . $class . ' d-inline-block text-center results-icon-md">' . $q->full_time . '</div>';
    }
}
