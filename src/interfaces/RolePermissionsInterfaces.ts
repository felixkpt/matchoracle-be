interface RoleInterface {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
  user_id: number;
  status: number;
}

interface PermissionInterface {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
  uri: string;
  title: string;
  icon: string | null;
  hidden: number;
  user_id: number;
  status: number;
  pivot: {
    role_id: number;
    permission_id: number;
  };
}

interface RouteInterface {
  uri: string;
  methods: string;
  uri_methods: string;
  slug: string;
  title: string;
  hidden: boolean;
  icon: string | null;
  checked: boolean;
}
interface RouteCollectionInterface {
  folder: string;
  routes: RouteInterface[];
  slug: string;
  title: string;
  hidden: boolean;
  icon: string | null;

  children: RouteCollectionInterface[];
}
