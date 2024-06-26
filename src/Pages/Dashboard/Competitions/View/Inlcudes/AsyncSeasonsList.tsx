import { SeasonsListInterface } from '@/interfaces/FootballInterface';
import Str from '@/utils/Str';
import Select from 'react-select';

const AsyncSeasonsList = ({ seasons, selectedSeason, handleSeasonChange }: SeasonsListInterface) => {

    return (
        <div>
            <Select
                className="form-control"
                classNamePrefix="select"
                defaultValue={selectedSeason}
                isDisabled={false}
                isLoading={false}
                isClearable={false}
                isSearchable={true}
                placeholder="Select season"
                name='season_id'
                options={seasons || []}
                onChange={(v) => handleSeasonChange && handleSeasonChange(v)}
                getOptionValue={(option: any) => `${option['id']}`}
                getOptionLabel={(option: any) => `${Str.before(option['start_date'], '-')} / ${Str.before(option['end_date'], '-')}`}
            />

        </div>
    )
}

export default AsyncSeasonsList