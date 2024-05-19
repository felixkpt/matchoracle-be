export interface RoleInterface {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
  user_id: number;
  status: number;
}

export interface PermissionInterface {
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

export interface RouteInterface {
  uri: string;
  methods: string;
  uri_and_methods: string;
  slug: string;
  title: string;
  hidden: boolean;
  icon: string | null;
  checked: boolean; 
  filename: string;
}
export interface RouteCollectionInterface {
  folder: string;
  routes: RouteInterface[];
  slug: string;
  title: string;
  hidden: boolean;
  icon: string | null;

  children: RouteCollectionInterface[];
}
