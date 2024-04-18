import useAxios from "@/hooks/useAxios";
import { useEffect, useState } from "react";
import TeamsNav from "./Includes/TeamNav";
import CountriesList from "./Includes/CountriesList";
import { CountryInterface } from "@/interfaces/FootballInterface";
import Loader from "@/components/Loader";
import NoContentMessage from "@/components/NoContentMessage";

const NationalTeams = () => {

    const { get, loading } = useAxios()
    const [countries, setCountries] = useState<CountryInterface[]>()

    useEffect(() => {
        get(`admin/countries/where-has-national-teams`).then((res) => {
            if (res) {
                setCountries(res.data)
            }
        })

    }, [])

    return (
        <div>
            <h5>National Teams</h5>
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

export default NationalTeams;
