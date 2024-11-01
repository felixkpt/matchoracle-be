import { useEffect } from "react";
import { config } from "../utils/helpers"

type Props = {
    title: string;
    hideTitle?: boolean; // Optional prop to hide the title if true
    description?: string;
}

const Header = ({ title, hideTitle, description }: Props) => {

    useEffect(() => {

        const head = document.querySelector('head')
        if (head) {
            let titleSelector = head.querySelector('title')
            if (!titleSelector) {
                titleSelector = document.createElement('title')

            }
            titleSelector.innerHTML = `${title ? title + ' - ' : ''}${config.name}`

            if (description) {
                let descriptionSelector = head.querySelector('meta[name="description"')

                if (!descriptionSelector) {
                    descriptionSelector = document.createElement('meta')
                    descriptionSelector.setAttribute('name=', 'description')
                }

                descriptionSelector.setAttribute('content', description)
            }
        }

    }, [title, hideTitle, description])

    return (
        <>
            {!hideTitle && <h4 className="page-title">{title}</h4>}
        </>
    )
}

export default Header