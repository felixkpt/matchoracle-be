import { CompetitionTabInterface } from "@/interfaces/FootballInterface"
import CompetitionHeader from "../Inlcudes/CompetitionHeader"
import GeneralModal from "@/components/Modals/GeneralModal"
import AsyncSeasonsList from "../Inlcudes/AsyncSeasonsList"
import Str from "@/utils/Str"
import { useEffect, useState } from "react"
import Composer from "@/utils/Composer"
import useAxios from "@/hooks/useAxios"
import Loader from "@/components/Loader"
import FormSummary from "@/components/Teams/FormSummary"
import DefaultMessage from "@/components/DefaultMessage"

const Statistics: React.FC<CompetitionTabInterface> = ({ record, selectedSeason, setSelectedSeason, setKey }) => {

    const competition = record
    if (!competition) return null

    const [key, setLocalKey] = useState(0);
    const [startDate, setStartDate] = useState(null);
    const [useDate, setUseDate] = useState(false);

    const { data, loading, errors, get } = useAxios()

    const statsUrl = `admin/competitions/view/${competition.id}/statistics?season_id=${selectedSeason ? selectedSeason?.id : ''}&date=${useDate ? startDate : ''}`

    useEffect(() => {

        get(statsUrl)

    }, [statsUrl])

    return (
        <div>
            {
                competition
                &&
                <div>
                    <CompetitionHeader title="Statistics" record={competition} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} startDate={startDate} setStartDate={setStartDate} setUseDate={setUseDate} setLocalKey={setLocalKey} />

                    <div className="card">
                        <div className="card-header">
                            <h6 className="d-flex gap-2 justify-content-between">
                                <div>
                                    <span>Stats for: </span>
                                    <span>{`${selectedSeason ? 'Season ' + Composer.season(selectedSeason) : startDate}`}</span>
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
                                                        <FormSummary teamWins={data.full_time_home_wins} draws={data.full_time_draws} teamLoses={data.full_time_away_wins} totals={100} winColorClass="bg-primary" labelA=" (1)" labelB=" (X)" labelC=" (2)" />
                                                        : ' N/A'
                                                }
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-6">
                                                1X2 Half time:
                                                {
                                                    data ?
                                                        <FormSummary teamWins={data.half_time_home_wins} draws={data.half_time_draws} teamLoses={data.half_time_away_wins} totals={100} winColorClass="bg-primary" labelA=" (1)" labelB=" (X)" labelC=" (2)" />
                                                        : ' N/A'
                                                }
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-12">
                                                BTS:
                                                {
                                                    data ?
                                                        <FormSummary teamWins={data.gg} draws={data.ng} totals={100} winColorClass="bg-primary" labelA=" (YES)" labelB=" (NO)" />
                                                        : ' N/A'
                                                }
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-12">
                                                OVER/UNDER 15:
                                                {
                                                    data ? <FormSummary teamWins={data.over15} draws={data.under15} totals={100} winColorClass="bg-primary" labelA=" (OVER)" labelB=" (UNDER)" /> : ' N/A'
                                                }
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-12">
                                                OVER/UNDER 25:
                                                {
                                                    data ? <FormSummary teamWins={data.over25} draws={data.under25} totals={100} winColorClass="bg-primary" labelA=" (OVER)" labelB=" (UNDER)" /> : ' N/A'
                                                }
                                            </div>
                                            <div className="col-12 col-md-12 col-lg-12">
                                                OVER/UNDER 35:
                                                {
                                                    data ? <FormSummary teamWins={data.over35} draws={data.under35} totals={100} winColorClass="bg-primary" labelA=" (OVER)" labelB=" (UNDER)" /> : ' N/A'
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

                    <GeneralModal title={`Predictions form`} actionUrl={`admin/competitions/view/${competition.id}/predict`} size={'modal-lg'} id={`doPredictions`} setKey={setKey}>
                        <div className="form-group mb-3">
                            <label htmlFor="season_id">Season</label>
                            <AsyncSeasonsList record={competition} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} useDate={useDate} />
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