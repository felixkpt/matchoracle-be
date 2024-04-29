import AutoTabs from "@/components/AutoTabs";
import Topics from "./Tabs/Topics";
import Categories from "./Tabs/Categories";
import PageHeader from "@/components/PageHeader";
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import useAxios from "@/hooks/useAxios";
import AutoModal from "@/components/AutoModal";
import Posts from "./Tabs/Posts";
import useListSources from "@/hooks/apis/useListSources";
import { PostCategoryInterface } from "@/interfaces/PostInterfaces";

export default function Index(): JSX.Element {

  const [category, setCategory] = useState<PostCategoryInterface>()
  const { posts: list_sources } = useListSources('n_id=' + (category?.id || '0'))

  const { slug } = useParams()

  const { data, loading, get } = useAxios()

  useEffect(() => {

    if (slug) {
      get(`/admin/posts/categories/${slug}`)
    }

  }, [slug])

  useEffect(() => {

    if (!loading && data) {

      const { data: data2, ...others } = data
      setCategory(data2)
      setModelDetails2(others)

    }

  }, [data])

  const [modelDetails2, setModelDetails2] = useState({})

  const tabs = [
    {
      name: "Posts",
      link: "posts",
      content: <Posts category={category} />,
    },
    {
      name: "Categories",
      link: "categories",
      content: <Categories category={category} />,
    },
    {
      name: "Topics",
      link: "topics",
      content: <Topics category={category} />,
    },
  ]

  return (
    <div className="mb-3">
      {category ?
        (
          <>
            <PageHeader title={category.name} action="button" actionText="Edit Category" actionTargetId="EditCat" permission='/admin/posts/categories' />

            <AutoTabs key={slug} tabs={tabs} active="posts" />
            {
              Object.keys(modelDetails2).length > 0 && <><AutoModal record={category} modelDetails={modelDetails2} actionUrl={`/admin/posts/categories/${category.id}`} id='EditCat' list_sources={list_sources} /></>
            }
          </>
        )
        :
        <div>Loading...</div>
      }

    </div>
  );
}
