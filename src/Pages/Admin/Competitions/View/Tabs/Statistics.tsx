import { CompetitionTabInterface, SeasonsListInterface } from "@/interfaces/FootballInterface"
import CompetitionHeader from "../Inlcudes/CompetitionHeader"
import GeneralModal from "@/components/Modals/GeneralModal"
import AsyncSeasonsList from "../Inlcudes/AsyncSeasonsList"
import { useEffect, useState } from "react"
import Composer from "@/utils/Composer"
import useAxios from "@/hooks/useAxios"
import Loader from "@/components/Loader"
import FormSummary from "@/components/Teams/FormSummary"
import DefaultMessage from "@/components/DefaultMessage"
import { useLocation } from "react-router-dom"
import FormatDate from "@/utils/FormatDate"
import { appendFromToDates } from "@/utils/helpers"

interface Props extends CompetitionTabInterface, SeasonsListInterface {}

const Statistics: React.FC<Props> = ({ record, seasons, selectedSeason, setSelectedSeason, setKey }) => {

    const competition = record
    if (!competition) return null

    const [key, setLocalKey] = useState(0);
    const initialDates: Array<Date | string | undefined> = [FormatDate.YYYYMMDD(new Date()), undefined];
    const [fromToDates, setFromToDates] = useState<Array<Date | string | undefined>>(initialDates);
    const [useDate, setUseDates] = useState(false);

    const location = useLocation();
    const queryParams = new URLSearchParams(location.search);
    const predictionTypeId = queryParams.get('prediction_type_id');

    const { data, loading, errors, get } = useAxios()
    const { data: dataPreds, loading: loadingPreds, errors: errorsPreds, get: getPreds } = useAxios()

    const statsUrl = `admin/competitions/view/${competition.id}/statistics?season_id=${selectedSeason ? selectedSeason?.id : ''}${appendFromToDates(useDate, fromToDates)}`
    const predsStatsUrl = `admin/competitions/view/${competition.id}/prediction-statistics?season_id=${selectedSeason ? selectedSeason?.id : ''}${appendFromToDates(useDate, fromToDates)}&prediction_type_id=${predictionTypeId || ''}`

    useEffect(() => {

        get(statsUrl)
        getPreds(predsStatsUrl)

    }, [statsUrl])

    function predsDisplay(preds: number, preds_true: number, preds_true_percentage: number) {
        return `${preds}, True: ${preds_true} (${preds_true_percentage}%)`
    }
    return (
        <div>
            {
                competition
                &&
                <div>
                    <CompetitionHeader title="Statistics" record={competition} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} fromToDates={fromToDates} setFromToDates={setFromToDates} setUseDates={setUseDates} setLocalKey={setLocalKey} />

                    <div className="card">
                        <div className="card-header">
                            <h6 className="d-flex gap-2 justify-content-between">
                                <div>
                                    <span>Stats for: </span>
                                    <span>{`${selectedSeason ? 'Season ' + Composer.season(selectedSeason) : fromToDates}`}</span>
                                </div>
                                <div>{`${(data && data.counts) ? 'Total matches: ' + data.counts : ''}`}</div>
                            </h6>
                        </div>
                        <div className="card-body">
                            {!loading ?
                                <div>
                                    {data ?
                                        <div className="row">
                                            <div className="col-12 col-md-12 col-lg-6">
                                                1X2 Full time:
                                                {
                                                    data ?
                                                        <FormSummary data1={data.full_time_home_wins} data2={data.full_time_draws} data3={data.full_time_away_wins} totals={100} data1ColorClass="bg-primary" label1=" (1)" label2=" (X)" label3=" (2)" />
                                                        : ' N/A'
                                                }
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-6">
                                                1X2 Half time:
                                                {
                                                    data ?
                                                        <FormSummary data1={data.half_time_home_wins} data2={data.half_time_draws} data3={data.half_time_away_wins} totals={100} data1ColorClass="bg-primary" label1=" (1)" label2=" (X)" label3=" (2)" />
                                                        : ' N/A'
                                                }
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-12">
                                                BTS:
                                                {
                                                    data ?
                                                        <FormSummary data1={data.gg} data2={data.ng} totals={100} data1ColorClass="bg-primary" label1=" (YES)" label2=" (NO)" />
                                                        : ' N/A'
                                                }
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-12">
                                                OVER/UNDER 15:
                                                {
                                                    data ? <FormSummary data1={data.over15} data2={data.under15} totals={100} data1ColorClass="bg-primary" label1=" (OVER)" label2=" (UNDER)" /> : ' N/A'
                                                }
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-12">
                                                OVER/UNDER 25:
                                                {
                                                    data ? <FormSummary data1={data.over25} data2={data.under25} totals={100} data1ColorClass="bg-primary" label1=" (OVER)" label2=" (UNDER)" /> : ' N/A'
                                                }
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-12">
                                                OVER/UNDER 35:
                                                {
                                                    data ? <FormSummary data1={data.over35} data2={data.under35} totals={100} data1ColorClass="bg-primary" label1=" (OVER)" label2=" (UNDER)" /> : ' N/A'
                                                }
                                            </div>
                                        </div>
                                        :
                                        <DefaultMessage />
                                    }
                                </div>
                                :
                                <Loader />
                            }
                        </div>
                    </div>
                    <hr className="my-5" />
                    <div className="card">
                        <div className="card-header">
                            <h6 className="d-flex gap-2 justify-content-between">
                                <div>
                                    <span>Prediction stats for: </span>
                                    <span>{`${selectedSeason ? 'Season ' + Composer.season(selectedSeason) : fromToDates}`}</span>
                                </div>
                                <div>{`${(dataPreds && dataPreds.counts) ? 'Total matches: ' + dataPreds.counts : ''}`}</div>
                            </h6>
                        </div>
                        <div className="card-body">
                            {!loadingPreds ?
                                <div>
                                    {dataPreds ?
                                        <div className="row">
                                            <div className="col-12 col-md-12 col-lg-6 mb-3">
                                                1X2 Full time:
                                                {
                                                    dataPreds ?
                                                        <FormSummary data1={dataPreds.full_time_home_wins_preds} data2={dataPreds.full_time_draws_preds} data3={dataPreds.full_time_away_wins_preds} totals={dataPreds.counts} data1ColorClass="bg-primary" label1=" (1)" label2=" (X)" label3=" (2)" />
                                                        : ' N/A'
                                                }
                                                <div>
                                                    <span className="me-2 border-4 border-end">Home wins preds:</span>
                                                    {
                                                        dataPreds ?
                                                            predsDisplay(dataPreds.full_time_home_wins_preds, dataPreds.full_time_home_wins_preds_true, dataPreds.full_time_home_wins_preds_true_percentage)
                                                            : ' N/A'
                                                    }
                                                </div>
                                                <div>
                                                    <span className="me-2 border-4 border-end">Draws preds:</span>
                                                    {
                                                        dataPreds ?
                                                            predsDisplay(dataPreds.full_time_draws_preds, dataPreds.full_time_draws_preds_true, dataPreds.full_time_draws_preds_true_percentage)
                                                            : ' N/A'
                                                    }
                                                </div>
                                                <div>
                                                    <span className="me-2 border-4 border-end">Away wins preds:</span>
                                                    {
                                                        dataPreds ?
                                                            predsDisplay(dataPreds.full_time_away_wins_preds, dataPreds.full_time_away_wins_preds_true, dataPreds.full_time_away_wins_preds_true_percentage)
                                                            : ' N/A'
                                                    }
                                                </div>
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-6 mb-3">
                                                BTS:
                                                {
                                                    dataPreds ?
                                                        <FormSummary data1={dataPreds.full_time_gg_preds} data2={dataPreds.full_time_ng_preds} totals={dataPreds.counts} label1=" (GG)" label2=" (NG)" />
                                                        : ' N/A'
                                                }
                                                <div>
                                                    <span className="me-2 border-4 border-end">GG Preds:</span>
                                                    {
                                                        dataPreds ?
                                                            predsDisplay(dataPreds.full_time_gg_preds, dataPreds.full_time_gg_preds_true, dataPreds.full_time_gg_preds_true_percentage)
                                                            : ' N/A'
                                                    }
                                                </div>
                                                <div>
                                                    <span className="me-2 border-4 border-end">NG Preds:</span>
                                                    {
                                                        dataPreds ?
                                                            predsDisplay(dataPreds.full_time_ng_preds, dataPreds.full_time_ng_preds_true, dataPreds.full_time_ng_preds_true_percentage)
                                                            : ' N/A'
                                                    }
                                                </div>
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-6 mb-3">
                                                OVER/UNDER 15:
                                                {
                                                    dataPreds ?
                                                        <FormSummary data1={dataPreds.full_time_over15_preds} data2={dataPreds.full_time_under15_preds} totals={dataPreds.counts} label1=" (OV15)" label2=" (UN15)" />
                                                        : ' N/A'
                                                }
                                                <div>
                                                    <span className="me-2 border-4 border-end">Over Preds:</span>
                                                    {
                                                        dataPreds ?
                                                            predsDisplay(dataPreds.full_time_over15_preds, dataPreds.full_time_over15_preds_true, dataPreds.full_time_over15_preds_true_percentage)
                                                            : ' N/A'
                                                    }
                                                </div>
                                                <div>
                                                    <span className="me-2 border-4 border-end">Under Preds:</span>
                                                    {
                                                        dataPreds ?
                                                            predsDisplay(dataPreds.full_time_under15_preds, dataPreds.full_time_under15_preds_true, dataPreds.full_time_under15_preds_true_percentage)
                                                            : ' N/A'
                                                    }
                                                </div>
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-6 mb-3">
                                                OVER/UNDER 25:
                                                {
                                                    dataPreds ?
                                                        <FormSummary data1={dataPreds.full_time_over25_preds} data2={dataPreds.full_time_under25_preds} totals={dataPreds.counts} label1=" (OV25)" label2=" (UN25)" />
                                                        : ' N/A'
                                                }
                                                <div>
                                                    <span className="me-2 border-4 border-end">Over Preds:</span>
                                                    {
                                                        dataPreds ?
                                                            predsDisplay(dataPreds.full_time_over25_preds, dataPreds.full_time_over25_preds_true, dataPreds.full_time_over25_preds_true_percentage)
                                                            : ' N/A'
                                                    }
                                                </div>
                                                <div>
                                                    <span className="me-2 border-4 border-end">Under Preds:</span>
                                                    {
                                                        dataPreds ?
                                                            predsDisplay(dataPreds.full_time_under25_preds, dataPreds.full_time_under25_preds_true, dataPreds.full_time_under25_preds_true_percentage)
                                                            : ' N/A'
                                                    }
                                                </div>
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-6">
                                                OVER/UNDER 35:
                                                {
                                                    dataPreds ?
                                                        <FormSummary data1={dataPreds.full_time_over35_preds} data2={dataPreds.full_time_under35_preds} totals={dataPreds.counts} label1=" (OV35)" label2=" (UN35)" />
                                                        : ' N/A'
                                                }
                                                <div>
                                                    <span className="me-2 border-4 border-end">Over Preds:</span>
                                                    {
                                                        dataPreds ?
                                                            predsDisplay(dataPreds.full_time_over35_preds, dataPreds.full_time_over35_preds_true, dataPreds.full_time_over35_preds_true_percentage)
                                                            : ' N/A'
                                                    }
                                                </div>
                                                <div>
                                                    <span className="me-2 border-4 border-end">Under Preds:</span>
                                                    {
                                                        dataPreds ?
                                                            predsDisplay(dataPreds.full_time_under35_preds, dataPreds.full_time_under35_preds_true, dataPreds.full_time_under35_preds_true_percentage)
                                                            : ' N/A'
                                                    }
                                                </div>
                                            </div>

                                            <div className="col-12 mt-4">
                                                <h6 className="d-flex gap-2 justify-content-between">
                                                    <div>{`${(dataPreds && dataPreds.average_score) ? 'Average Score: ' + dataPreds.average_score : '0'}%`}</div>
                                                </h6>
                                            </div>

                                        </div>
                                        :
                                        <DefaultMessage />
                                    }
                                </div>
                                :
                                <Loader />
                            }
                        </div>
                    </div>

                    <GeneralModal title={`Predictions form`} actionUrl={`admin/competitions/view/${competition.id}/predict`} size={'modal-lg'} id={`doPredictions`} setKey={setKey}>
                        <div className="form-group mb-3">
                            <label htmlFor="season_id">Season</label>
                            <AsyncSeasonsList seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} useDate={useDate} />
                        </div>
                        <div className="form-group mb-3">
                            <label htmlFor="matchday">Match day</label>
                            <input type="number" min={0} max={200} name='matchday' id='matchday' className='form-control' />
                        </div>
                        <div className="modal-footer gap-1">
                            <button type="button" className="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" className="btn btn-primary">Submit</button>
                        </div>
                    </GeneralModal>
                </div>
            }
        </div>
    )
}

export default Statistics