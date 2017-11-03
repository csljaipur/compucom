package com.compucom.gpstracker.android.gpstracker;

import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v7.app.AppCompatActivity;
import android.widget.ListView;
import android.widget.SimpleAdapter;

import com.compucom.gpstracker.R;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Amit Sharma on 21-07-2017.
 */

public class Notification extends AppCompatActivity {
    ListView listView;
    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.notification);
        listView = (ListView)findViewById(R.id.notifyList);
        ArrayList<HashMap<String,String >> arrayList =null;
        String key[] = {"heading","time"};
        int ids[] = {R.id.notifyHeading,R.id.notifyTime};
        SimpleAdapter adapter = new SimpleAdapter(Notification.this,arrayList,R.layout.notifyiems
            ,key,ids);
    }
}
