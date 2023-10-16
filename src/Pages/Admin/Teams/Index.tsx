import { NavLink, useLocation, useNavigate } from "react-router-dom";
import CompetitionsList from "./Includes/CompetitionsList";
import useAxios from "@/hooks/useAxios";
import { useEffect, useState } from "react";
import TeamsNav from "./View/TeamNav";
import { CountryInterface } from "@/interfaces/CompetitionInterface";

const Index = () => {

    const location = useLocation();

    // Access different parts of the location object
    const { pathname } = location;
    const navigate = useNavigate();
    const { get } = useAxios()
    const [countries, setCountries] = useState<any[]>()

    useEffect(() => {
        if (pathname) {
            if (!pathname.endsWith('club-teams') && !pathname.endsWith('national-teams'))
                navigate('/admin/teams/club-teams')
            else {
                const suffix = pathname.endsWith('club-teams') ? 'club-teams' : 'national-teams'

                get(`admin/countries/where-has-${suffix}`).then((res) => {
                    if (res) {
                        setCountries(res.data)
                    }
                })
            }
        }

    }, [pathname])

    return (
        <div>
            <h5>Teams</h5>
            <TeamsNav />
            <div className="accordion" id="countriesAccordion">
                {countries && countries.map((country: CountryInterface) => (
                    <div className="accordion-item mb-1" key={country.id}>
                        <h2 className="accordion-header" id="headingTwo">
                            <button className="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target={`#collapse${country.id}`} aria-expanded="false" aria-controls={`collapse${country.id}`}>
                                <NavLink to={`/admin/countries/view/${country.id}`} onClick={(e) => e.preventDefault()} className="text-decoration-none text-dark">
                                    <img src={`${country.flag}`} className="rounded-circle me-2 bg-body-secondary border" style={{ width: "28px", height: "28px" }} alt="" /> <span>{country.name}</span>
                                </NavLink>
                            </button>
                        </h2>
                        <div id={`collapse${country.id}`} className="accordion-collapse collapse" aria-labelledby={`#heading${country.id}`}>
                            <div className="accordion-body">
                                <div className="ml-14"><CompetitionsList country={country} competitions={country.competitions} /></div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default Index;
