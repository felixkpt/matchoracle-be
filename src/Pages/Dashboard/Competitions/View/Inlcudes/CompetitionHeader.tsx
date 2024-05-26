import { CompetitionInterface, SeasonInterface } from "@/interfaces/FootballInterface"
import Str from "@/utils/Str"
import { competitionLogo, countryLogo } from "@/utils/helpers"
import { NavLink } from "react-router-dom"
import Select from 'react-select';

type CompetitionHeaderProps = {
    competition: CompetitionInterface
    currentTab: string | undefined
    seasons: SeasonInterface[] | null
    selectedSeason: SeasonInterface | null
    setSelectedSeason: any
    setFromToDates?: any
    setUseDates?: any
    setKey?: any
}
const CompetitionHeader = ({ competition, currentTab, seasons, selectedSeason, setSelectedSeason, setFromToDates, setUseDates, setKey }: CompetitionHeaderProps) => {

    function handleSetSelectedSeason(season: SeasonInterface) {
        setSelectedSeason(season)
        if (typeof setFromToDates === 'function') {
            setUseDates(false)
            setFromToDates([])
        }
        if (typeof setKey === 'function') {
            setKey((curr: number) => curr += 1)
        }
    }
    return (
        <div className='header-title shadow-sm p-2 rounded mb-4 row no-select'>
            <div className="col-12">
                <div className="d-flex gap-3">
                    <img className="compe-logo" src={competitionLogo(competition.logo)} alt="" />
                    <div className="d-flex align-items-center gap-4">
                        <h5 className="row align-items-center gap-2">
                            <span><span>{competition.name}</span><span>{currentTab ? ' - ' + currentTab : ''}</span></span>
                            <div className="d-flex gap-1">
                                <small className="d-flex align-items-center gap-2">
                                    <NavLink to={`/dashboard/countries/view/${competition.country.id}`} className="d-flex align-items-center btn-link">
                                        <img className="symbol-image-sm me-1" src={countryLogo(competition?.country.flag)} alt="" />{competition.country.name}
                                    </NavLink>
                                </small>
                                <div>
                                    {
                                        competition
                                        &&
                                        <Select
                                            key={selectedSeason ? selectedSeason.id : 0}
                                            className="form-control border-0"
                                            classNamePrefix="select"
                                            defaultValue={selectedSeason}
                                            isDisabled={false}
                                            isLoading={false}
                                            isClearable={false}
                                            isSearchable={false}
                                            placeholder="Select season"
                                            name='season_id'
                                            options={seasons || []}
                                            onChange={(v: any) => handleSetSelectedSeason(v)}
                                            getOptionValue={(option: any) => `${option['id']}`}
                                            getOptionLabel={(option: any) => `${Str.before(option['start_date'], '-')} / ${Str.before(option['end_date'], '-')}`}
                                        />
                                    }
                                </div>
                            </div>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default CompetitionHeader