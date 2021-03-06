<?php

    class ProjectController extends BaseController
    {
        public function index()
        {
            $parents = Project::where('parent_id', '=', 0)->orderBy('title')->get();

            return View::make('projects.index')
                ->with('page_title', 'Projects')
                ->with('projects', $parents);
        }

        public function create()
        {
            $parents = Project::where('parent_id', '=', 0)->orderBy('title')->lists('title', 'id');

            return View::make('projects.create')
                ->with('page_title', 'Add project')
                ->with('parents', $parents);
        }

        public function edit($id)
        {
            $project = Project::find($id);
            $clients=User::where('role_id', '=',5)->orderBy('id')->lists('last_name', 'id');

            if ($project) {
                $parent = Project::find($project->parent_id);

                return View::make('projects.edit')
                    ->with('page_title', 'Edit project')
                    ->with('project', $project)
                    ->with('clients', $clients)
                    ->with('parent', $parent);
            }

            Session::flash('errorMsg', 'You do not have access to edit this project.');
            return Redirect::route('projects.index');
        }

        public function update($id)
        {
            $rules = array('title' => 'required',);
            $validator = Validator::make(Input::all(), $rules);

            if ($validator->fails()) {
                return Redirect::route('projects.edit', array('id' => $id))
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $project = Project::find($id);

                if ($project) {
                    $project->title = Input::get('title');
                    $project->client_id = Input::get('client_id');
                    $project->save();

                    Session::flash('successMsg', 'Successfully updated project!');
                    return Redirect::route('projects.index');
                }

                Session::flash('errorMsg', 'You do not have access to edit this project.');
                return Redirect::route('projects.index');

            }
        }

        public function store()
        {
            $rules = array(
                'title'     => 'required',
                'parent_id' => 'required|numeric',
            );

            $validator = Validator::make(Input::all(), $rules);

            if ($validator->fails()) {
                return Redirect::route('projects.create')
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $project = new Project;
                $project->title = Input::get('title');
                $project->parent_id = Input::get('parent_id');
                $project->save();

                Session::flash('successMsg', 'Successfully created project!');
                return Redirect::route('projects.index');
            }
        }

        public function destroy($id)
        {
            $project = Project::find($id);

            if ($project) {
                if ($project->hasHoursOrChilds()) {
                    Session::flash('errorMsg', 'You do not have access to delete this project.');
                    return Redirect::route('projects.index');
                }

                $project->delete();

                Session::flash('successMsg', 'Successfully deleted the project!');
                return Redirect::route('projects.index');
            }

            Session::flash('errorMsg', 'You do not have access to delete this project.');
            return Redirect::route('projects.index');
        }

        public function childs()
        {
            $projects = Project::where('parent_id', '=', Input::get('option'))->orderBy('title')->lists('title', 'id');

            return Response::json($projects);
        }
    }
