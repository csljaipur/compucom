package com.compucom.gpstracker.android.gpstracker;

import android.os.AsyncTask;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.widget.TextView;

import com.compucom.gpstracker.R;

import java.text.SimpleDateFormat;
import java.util.Date;

/**
 * Created by Amit Sharma on 26-07-2017.
 */

public class DisplayNotification extends AppCompatActivity {
    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.displaynotification);
        TextView title = (TextView) findViewById(R.id.displayNotificationTitle);
        TextView message = (TextView) findViewById(R.id.displayNotificationMessage);
        title.setText(getIntent().getStringExtra("title"));
        message.setText(getIntent().getStringExtra("message"));

        new Acknowladgement().execute(getIntent().getStringExtra("id"));
    }

    class Acknowladgement extends AsyncTask<String,Void,Void> {
        @Override
        protected Void doInBackground(String... params) {
            HttpHandler httpHandler = new HttpHandler();
            String id = params[0];
            SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
            String currentDateandTime = sdf.format(new Date());
            String result = httpHandler.makeServiceCall("http://jantv.in/gps/includes/pages/appSendAcknowledgement.php?id="+id+"&acktype=delivered&status=1&sendtime="+currentDateandTime+"");
            Log.e("DisplayNotification","http://jantv.in/gps/includes/pages/appSendAcknowledgement.php?id="+id+"&acktype=read&status=1&sendtime="+currentDateandTime+"");
            Log.e("DisplayNotification",result);
            return null;
        }
    }
}
