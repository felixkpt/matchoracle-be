<?php

namespace App\Utilities;

use App\Repositories\GameComposer;

trait FormatPredictionTrait
{
    function formatted_prediction($q)
    {
        return $q->{$this->predictionTypeMode};
    }

    protected function formatFTHDAProba($q)
    {
        $class = 'border-start text-dark';

        $pred = $q->{$this->predictionTypeMode};
        $str = '-';
        if ($pred) {
            $str = $pred->ft_home_win_proba . '%, ' . $pred->ft_draw_proba . '%, ' . $pred->ft_away_win_proba . '%';
        }

        return '<div class="border-4 ps-1 text-nowrap ' . $class . ' d-inline-block">' . $str . '</div>';
    }

    protected function formatFTHDAPick($q)
    {
        $has_res = GameComposer::hasResults($q);
        $res = GameComposer::winningSide($q, true);

        $class = 'bg-light-blue text-dark';

        $pred = $q->{$this->predictionTypeMode};
        $str = '-';
        if ($pred && $has_res) {
            $str = $pred->ft_hda_pick == 0 ? '1' : ($pred->ft_hda_pick == 1 ? 'X' : '2');
            if ($pred->ft_hda_pick == $res) {
                $class = 'border-bottom bg-success text-white';
            } elseif ($pred) {
                $class = 'border-bottom border-danger text-danger';
            }
        }

        return '<div class="rounded-circle border p-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $str . '</div>';
    }

    protected function formatHTHDAProba($q)
    {
        $class = 'border-start text-dark';

        $pred = $q->{$this->predictionTypeMode};
        $str = '-';
        if ($pred) {
            $str = $pred->ft_home_win_proba . '%, ' . $pred->ft_draw_proba . '%, ' . $pred->ft_away_win_proba . '%';
        }

        return '<div class="border-4 ps-1 text-nowrap ' . $class . ' d-inline-block">' . $str . '</div>';
    }

    protected function formatHTHDAPick($q)
    {
        $has_res = GameComposer::hasResults($q);
        $res = GameComposer::winningSide($q, true);

        $class = 'bg-light-blue text-dark';

        $pred = $q->{$this->predictionTypeMode};
        $str = '-';
        if ($pred && $has_res) {
            $str = $pred->ft_hda_pick == 0 ? '1' : ($pred->ft_hda_pick == 1 ? 'X' : '2');
            if ($pred->ft_hda_pick == $res) {
                $class = 'border-bottom bg-success text-white';
            } elseif ($pred) {
                $class = 'border-bottom border-danger text-danger';
            }
        }

        return '<div class="rounded-circle border p-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $str . '</div>';
    }

    protected function formatBTS($q)
    {
        $has_res = GameComposer::hasResults($q);
        $res = GameComposer::bts($q, true);

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->{$this->predictionTypeMode};
        $str = '-';
        if ($pred && $has_res) {
            $str = $pred->bts_pick == 1 ? 'YES' : 'NO';

            if ($pred->bts_pick == $res) {
                $class = 'border-bottom border-success';
            } elseif ($pred) {
                $class = 'border-bottom border-danger ';
            }
        }


        return '<div class="border-2 py-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $str . '</div>';
    }

    protected function formatGoals($q)
    {
        $has_res = GameComposer::hasResults($q);
        $res = GameComposer::goals($q, true);

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->{$this->predictionTypeMode};

        $str = '-';
        if ($pred && $has_res) {
            $str = $pred->over_under25_pick == 1 ? 'OV' : 'UN';

            if ($pred->over_under25_pick && $res > 2) {
                $class = 'border-bottom border-success';
            } elseif (!$pred->over_under25_pick && $res <= 2) {
                $class = 'border-bottom border-success';
            } elseif ($pred) {
                $class = 'border-bottom border-danger';
            }
        }


        return '<div class="border-2 py-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $str . '</div>';
    }

    protected function formatCS($q)
    {

        $has_res = GameComposer::hasResults($q);

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->{$this->predictionTypeMode};

        $str = '';
        if ($pred && $has_res) {

            $q->prediction = $q->{$this->predictionTypeMode};
            $res = GameComposer::cs($q);

            if ($res) {
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

        return '<div class="border-4 p-1 text-nowrap ' . $class . ' d-inline-block text-center results-icon-md">' . $q->half_time . '</div>';
    }

    protected function formatFTScores($q)
    {

        $class = 'border-start text-dark';

        return '<div class="border-4 p-1 text-nowrap ' . $class . ' d-inline-block text-center results-icon-md">' . $q->full_time . '</div>';
    }
}