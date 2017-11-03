package com.compucom.gpstracker.android.gpstracker;

import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.os.Handler;
import android.os.IBinder;
import android.provider.Settings;
import android.support.annotation.Nullable;
import android.support.v4.app.NotificationCompat;
import android.text.format.Time;
import android.util.Log;

import com.compucom.gpstracker.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;

/**
 * Created by Amit Sharma on 29-03-2017.
 */

public class Notification_Servise extends Service {
    public static final String MyPREFERENCES = "CompucomGPSTracker";

    @Nullable
    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        final Handler handler = new Handler();
        handler.postDelayed(new Runnable() {
            @Override
            public void run() {
                //Toast.makeText(Notification_Servise.this, "service Started", Toast.LENGTH_SHORT).show();
                SharedPreferences sharedPreferences = getSharedPreferences(MyPREFERENCES, Context.MODE_PRIVATE);
                String s = sharedPreferences.getString("notification", "");
                if (s.equals("OFF")) {
                } else new GetHttpResponse(getBaseContext()).execute();
                handler.postDelayed(this, 15000);
            }
        }, 0);

        return super.START_STICKY_COMPATIBILITY;
    }


    private class GetHttpResponse extends AsyncTask<Void, Void, Void> {

        private Context context;
        String result;
        ArrayList<HashMap<String, String>> notificationList = new ArrayList<>();

        public GetHttpResponse(Context context) {
            this.context = context;
        }

        @Override
        protected void onPreExecute() {
            super.onPreExecute();
        }

        @Override
        protected Void doInBackground(Void... params) {
            SharedPreferences sharedPreferences = getSharedPreferences("com.websmithing.gpstracker.prefs", Context.MODE_PRIVATE);
            String number = sharedPreferences.getString("userName", null);
            HttpHandler httpHandler = new HttpHandler();
            result = httpHandler.makeServiceCall("http://jantv.in/gps/includes/pages/appFetchNotification.php?mobileno=" + number);

            if (result != null) {
                Log.d("Result", result);

                try {
//                  JSONArray jsonArray = new JSONArray(result);
//                    for (int i=0;i<jsonArray.length();i++){
//                        JSONObject object = jsonArray.getJSONObject(i);
                    JSONObject object = new JSONObject(result);
                    HashMap<String, String> hashMap = new HashMap<>();
                    String id = object.getString("id");
                    String title = object.getString("title");
                    String message = object.getString("message");
                    String time = object.getString("sendtime");

                    hashMap.put("id", id);
                    hashMap.put("title", title);
                    hashMap.put("message", message);
                    hashMap.put("time", time);
                    notificationList.add(hashMap);
//                    }

                } catch (JSONException e) {
                    // TODO Auto-generated catch block
                    e.printStackTrace();
                }
            }

            return null;
        }

        @Override
        protected void onPostExecute(Void result) {
            SharedPreferences sharedPreferences;
            SharedPreferences.Editor editor;

            sharedPreferences = getSharedPreferences(MyPREFERENCES, Context.MODE_PRIVATE);
            editor = sharedPreferences.edit();
            Date date = null;
            Time time = null;

//            String news = sharedPreferences.getString("Letest_News","");
//            Gson gson = new Gson();
//            ArrayList<News> list = gson.fromJson(news, new TypeToken<ArrayList<News>>() {}.getType());
//            Log.e("Notification ","before if condition call");
//            if (newsList.size()!=0) {
//                News news1 = list.get(0);
//                SimpleDateFormat dateFormatter = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
//                try {
//                    date = dateFormatter.parse(news1.news_dt);
//                }catch (ParseException e){
//                    e.printStackTrace();
//                }
//
//
//
//                for (int i = 0; i<30;i++){
//                    Date date1 =null;
//                    News news2 = newsList.get(i);
//                    try {
//                     date1 = dateFormatter.parse(news2.news_dt);
//                    }catch (ParseException e){
//                        e.printStackTrace();
//                    }
//
//                    if (date.compareTo(date1)<0){
//
//                        Intent intent = new Intent(Notification_Servise.this, NotificationNewsDetail.class);
//                        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
//                        intent.putExtra("newsid",news1.newsid);
//                        intent.putExtra("title",news1.news_title);
//                        intent.putExtra("heading",news1.news_head);
//                        intent.putExtra("time",news1.news_dt);
//                        intent.putExtra("desc",news1.news_desc);
//                        intent.putExtra("image",news1.image);
//
//                        PendingIntent pendingIntent = PendingIntent.getActivity(Notification_Servise.this, 0, intent, PendingIntent.FLAG_ONE_SHOT);
//
//                        NotificationCompat.Builder builder = new NotificationCompat.Builder(Notification_Servise.this)
//                                .setSmallIcon(R.drawable.ic_launcher)
//                                .setContentTitle(news1.news_head)
//                                .setContentText(news1.news_title)
//                                .setAutoCancel(true)
//                                .setContentIntent(pendingIntent);
//
//                        NotificationManager notificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
//                        notificationManager.notify(Integer.parseInt(news1.newsid), builder.build());
//                        continue;
//                    }
//                    else
//                    {
//                        String json = gson.toJson(newsList);
//                        editor.putString("Latest_News",json);
//                        editor.commit();
//                        break;
//                    }
//                }
//
//
//
//
//            }
            for (int i = 0; i < notificationList.size(); i++) {
                HashMap<String, String> hashMap = notificationList.get(i);
                Intent intent = new Intent(Notification_Servise.this, DisplayNotification.class);
                intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
                intent.putExtra("title", hashMap.get("title"));
                intent.putExtra("message", hashMap.get("message"));
                intent.putExtra("id",hashMap.get("id"));
                PendingIntent pendingIntent = PendingIntent.getActivity(Notification_Servise.this, 0, intent, PendingIntent.FLAG_ONE_SHOT);

                NotificationCompat.Builder builder = new NotificationCompat.Builder(Notification_Servise.this)
                        .setSmallIcon(R.drawable.ic_launcher)
                        .setContentTitle(hashMap.get("title"))
                        .setContentText(hashMap.get("message") + "\n" + hashMap.get("time"))
                        .setAutoCancel(true)
                        .setContentIntent(pendingIntent);
                builder.setSound(Settings.System.DEFAULT_NOTIFICATION_URI);
                builder.setVibrate(new long[] { 1000, 1000});
                NotificationManager notificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
                notificationManager.notify(Integer.parseInt(hashMap.get("id")), builder.build());


                new Acknoladgement().execute( hashMap.get("id"));
            }

        }

        class Acknoladgement extends AsyncTask<String,Void,Void>{
            @Override
            protected Void doInBackground(String... params) {
                HttpHandler httpHandler = new HttpHandler();
                String id = params[0];
                SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
                String currentDateandTime = sdf.format(new Date());
                String result1 = httpHandler.makeServiceCall("http://jantv.in/gps/includes/pages/appSendAcknowledgement.php?id="+id+"&acktype=delivered&status=1&sendtime="+currentDateandTime+"");
                return null;
            }
        }
    }
}
