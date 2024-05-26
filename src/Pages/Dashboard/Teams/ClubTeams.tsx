import { useLocation, useNavigate } from "react-router-dom";
import useAxios from "@/hooks/useAxios";
import { useEffect, useState } from "react";
import TeamsNav from "./Includes/TeamNav";
import { CountryInterface } from "@/interfaces/FootballInterface";
import CountriesList from "./Includes/CountriesList";
import Loader from "@/components/Loader";
import NoContentMessage from "@/components/NoContentMessage";

const ClubTeams = () => {

    const location = useLocation();
    // Access different parts of the location object
    const { pathname } = location;
    const navigate = useNavigate();

    const { get, loading } = useAxios()
    const [countries, setCountries] = useState<CountryInterface[]>()

    useEffect(() => {
        if (pathname) {
            if (!pathname.endsWith('club-teams')) {
                navigate('/dashboard/teams/club-teams')
            } else {
                get(`dashboard/countries/where-has-club-teams`).then((res): void => {
                    if (res) {
                        setCountries(res.data.data)
                    }
                })
            }
        }
    }, [pathname])

    return (
        <div>
            <h5>Club Teams</h5>
            <TeamsNav />
            {
                countries && countries?.length > 0 ?
                    <CountriesList countries={countries} />
                    :
                    <>
                        {
                            loading ?
                                <Loader />
                                :
                                <NoContentMessage />
                        }
                    </>
            }
        </div>
    );
};

export default ClubTeams;
