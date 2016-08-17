package com.example.teju_chi.androidproject;


import android.content.Context;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarDrawerToggle;
import android.support.v7.internal.widget.AdapterViewCompat;
import android.support.v7.widget.Toolbar;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;


/**
 * A simple {@link Fragment} subclass.
 */
public class NavigationBarFragment extends Fragment implements AdapterViewCompat.OnItemClickListener {
    private ActionBarDrawerToggle actionBarDrawerToggle;
    private DrawerLayout mdrawerLayout;
    NavigatorListArrayAdapter navigatorListArrayAdapter;
    Context context;

    public NavigationBarFragment() {
        // Required empty public constructor
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        return inflater.inflate(R.layout.fragment_navigation_bar, container, false);
    }

    public void setUp(DrawerLayout drawerLayout,Toolbar toolbar){
        context=getActivity();
        ListView listview=(ListView)getView().findViewById(R.id.navigatorList);
        navigatorListArrayAdapter=new NavigatorListArrayAdapter(context);
        listview.setAdapter(navigatorListArrayAdapter);
        //listview.setOnItemClickListener(navigatorListArrayAdapter);

        mdrawerLayout=drawerLayout;
        actionBarDrawerToggle=new ActionBarDrawerToggle(getActivity(),mdrawerLayout,toolbar,R.string.open,
                R.string.close){
            @Override
            public void onDrawerOpened(View drawerView)
            {
                super.onDrawerOpened(drawerView);
            }

            @Override
            public void onDrawerClosed(View drawerView) {
                super.onDrawerClosed(drawerView);
            }
        };
        mdrawerLayout.setDrawerListener(actionBarDrawerToggle);
        actionBarDrawerToggle.syncState();
    }

    @Override
    public void onItemClick(AdapterViewCompat<?> parent, View view, int position, long id) {

    }
}
