import { CountryInterface } from '@/interfaces/FootballInterface'
import { renderCountryLogo } from '@/utils/helpers'
import { NavLink } from 'react-router-dom'
import CompetitionsList from './CompetitionsList'
import { Icon } from '@iconify/react/dist/iconify.js'

interface Props {
    countries: CountryInterface[]
}

const CountriesList = ({ countries }: Props) => {
    return (
        <div className="accordion" id="countriesAccordion">
            {countries && countries.map((country: CountryInterface) => (
                <div className="accordion-item mb-1" key={country.id}>
                    <h2 className="accordion-header" id="headingTwo">
                        <button className="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target={`#collapse${country.id}`} aria-expanded="false" aria-controls={`collapse${country.id}`}>
                            <NavLink to={`/dashboard/countries/view/${country.id}`} onClick={(e) => e.preventDefault()} className="text-decoration-none text-dark">
                                <img src={`${renderCountryLogo(country.flag)}`} className="rounded-circle me-2 bg-body-secondary border" style={{ width: "28px", height: "28px" }} alt="" /> <span>{country.name}</span>
                            </NavLink>
                        </button>
                    </h2>
                    <div id={`collapse${country.id}`} className="accordion-collapse collapse" aria-labelledby={`#heading${country.id}`}>
                        <div className="accordion-body">
                            <div className="d-flex justify-content-end"><NavLink to={`/dashboard/countries/view/${country.id}`} className="link-unstyled shadow rounded m-1 p-2 hover-grow text-muted"><span className="me-1">Country competitions info</span><Icon icon={'mdi:arrow-right-bold'} /></NavLink></div>
                            <div className="ml-14"><CompetitionsList country={country} competitions={country.competitions} /></div>
                        </div>
                    </div>
                </div>
            ))}
        </div>
    )
}

export default CountriesList