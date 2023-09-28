import { usePage } from "@inertiajs/react";
import DefaultLayout from "@/Layouts/DefaultLayout";
import CompetitionsList from "./CompetitionsList";

interface CountryInterface {
    id: string,
    name: string,
    slug: string,
    competitions: []

}

const Index = () => {

    const { props } = usePage<any>();
    const { countries } = props

    return (
        <DefaultLayout title="Teams list">
            <div>
                {countries && countries.map((country: CountryInterface) => (
                    <div key={country.id} className="flex flex-col w-full my-2">
                        <div className="flex items-center gap-2 cursor-pointer">
                            <div className="w-8 h-8 bg-white rounded-full inline-block"></div>
                            <div className="inline-block">{country.name}</div>
                        </div>
                        <div className="ml-14"><CompetitionsList competitions={country.competitions} /></div>
                    </div>
                ))}
            </div>
        </DefaultLayout>
    );
};

export default Index;
