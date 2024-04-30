import { CountryInterface } from "@/interfaces/FootballInterface"
import { countryLogo } from "@/utils/helpers"

type CountryHeaderProps = {
    country: CountryInterface
    currentTab: string | undefined
}
const CountryHeader = ({ country, currentTab }: CountryHeaderProps) => {
    return (
        <div className='header-title shadow-sm p-2 rounded mb-3 row'>
            <div className="col-12 overflow-x-hidden">
                <div className="d-flex gap-3">
                    <img className="symbol-image-md" src={countryLogo(country.flag)} alt="" />
                    <div className="d-flex align-items-center gap-4">
                        <h5 className="row align-items-center gap-2">
                            <span>{country.name}{currentTab ? ' - ' + currentTab : ''}</span>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default CountryHeader