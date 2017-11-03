package com.compucom.gpstracker.android.gpstracker;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;

/**
 * Created by Amit Sharma on 28-07-2017.
 */

public class Receiver extends BroadcastReceiver {
    @Override
    public void onReceive(Context context, Intent intent) {
        context.startService(new Intent(context,Notification_Servise.class));
    }
}
